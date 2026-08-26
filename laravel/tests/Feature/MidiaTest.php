<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\EducationType;
use App\Enums\UserRole;
use App\Filament\Pages\Configuracoes;
use App\Filament\Resources\EducationItems\Pages\CreateEducationItem;
use App\Filament\Resources\EducationItems\Pages\EditEducationItem;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Media\Pages\EditMedia;
use App\Models\Configuracao;
use App\Models\EducationItem;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Rules\ArquivoDeMidia;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Tests\TestCase;

class MidiaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function usuario(UserRole $papel): User
    {
        $user = User::factory()->create(['role' => $papel]);
        $user->saveAppAuthenticationSecret('BASE32SECRETOTESTE');

        return $user;
    }

    /** Arquivo de verdade: a regra le a assinatura do conteudo, nao a extensao. */
    private function imagem(string $formato, int $largura, int $altura): UploadedFile
    {
        $tela = imagecreatetruecolor($largura, $altura);
        $caminho = tempnam(sys_get_temp_dir(), 'mid').".{$formato}";

        match ($formato) {
            'webp' => imagewebp($tela, $caminho, 82),
            'jpeg' => imagejpeg($tela, $caminho, 82),
            'png' => imagepng($tela, $caminho),
            'gif' => imagegif($tela, $caminho),
            'avif' => imageavif($tela, $caminho, 60),
        };

        imagedestroy($tela);

        return new UploadedFile($caminho, "foto.{$formato}", null, null, true);
    }

    private function erroDe(UploadedFile $arquivo, array $extensoes = []): ?string
    {
        $validator = Validator::make(
            ['arquivo' => $arquivo],
            ['arquivo' => [new ArquivoDeMidia($extensoes)]],
        );

        return $validator->errors()->first('arquivo') ?: null;
    }

    // ── validacao no servidor ────────────────────────────────────────

    public function test_webp_dentro_dos_limites_passa(): void
    {
        $this->assertNull($this->erroDe($this->imagem('webp', 1200, 800)));
    }

    public function test_jpeg_e_aceito_e_guardado_convertido_em_webp(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->assertNull($this->erroDe($this->imagem('jpeg', 800, 600)));

        $media = Media::daUpload($this->imagem('jpeg', 800, 600));

        $this->assertSame('image/webp', $media->mime_type);
        $this->assertStringEndsWith('.webp', $media->path);
        $this->assertSame('foto.jpeg', $media->original_name);
    }

    public function test_imagem_larga_demais_e_reduzida_para_1920px(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->assertNull($this->erroDe($this->imagem('webp', 2400, 1000)));

        $media = Media::daUpload($this->imagem('webp', 2400, 1000));

        $this->assertSame(ArquivoDeMidia::LARGURA_MAXIMA, $media->width);
        // Proporcao mantida: 2400x1000 reduzido para 1920 de largura.
        $this->assertSame(800, $media->height);
    }

    public function test_qualquer_formato_de_imagem_entra_e_sai_em_webp(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        foreach (['png', 'gif', 'avif'] as $formato) {
            $this->assertNull($this->erroDe($this->imagem($formato, 400, 300)), $formato);
            $this->assertSame('image/webp', Media::daUpload($this->imagem($formato, 400, 300))->mime_type, $formato);
        }
    }

    public function test_formato_que_o_servidor_nao_abre_diz_o_que_fazer(): void
    {
        // HEIC do iPhone: o GD nao abre, entao a tela precisa dizer isso em
        // vez de recusar sem motivo aparente.
        $caminho = tempnam(sys_get_temp_dir(), 'mid').'.heic';
        file_put_contents($caminho, "\x00\x00\x00\x18ftypheic".str_repeat('0', 64));

        $erro = (string) $this->erroDe(new UploadedFile($caminho, 'foto.heic', null, null, true));

        $this->assertStringContainsString('JPEG', $erro);
    }

    public function test_envio_de_gif_pelo_formulario_do_item_e_aceito(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::withQueryParams(['tipo' => EducationType::Livro->value])
            ->test(CreateEducationItem::class)
            ->fillForm([
                'title' => 'Livro com GIF',
                'image_id' => [$this->envio($this->imagem('gif', 600, 400))],
            ])
            ->call('salvarRascunho')
            ->assertHasNoFormErrors();

        $this->assertSame('image/webp', Media::sole()->mime_type);
    }

    public function test_imagem_gigante_demais_e_recusada(): void
    {
        // Area acima de 30 milhoes de pixels estoura a memoria do GD antes
        // de qualquer conversao: e barrada na validacao, nao no redimensionar.
        $erro = (string) $this->erroDe($this->imagem('webp', 8000, 4000));

        $this->assertStringContainsString('30 milhões', $erro);
    }

    public function test_arquivo_de_tipo_desconhecido_e_recusado(): void
    {
        $caminho = tempnam(sys_get_temp_dir(), 'mid').'.pdf';
        file_put_contents($caminho, 'isto nao e um PDF, e texto disfarcado de PDF');

        $erro = $this->erroDe(new UploadedFile($caminho, 'material.pdf', null, null, true));

        $this->assertStringContainsString('não corresponde', (string) $erro);
    }

    public function test_extensao_fora_da_lista_e_recusada(): void
    {
        $caminho = tempnam(sys_get_temp_dir(), 'mid').'.exe';
        file_put_contents($caminho, 'qualquer coisa');

        $this->assertStringContainsString(
            'não aceito',
            (string) $this->erroDe(new UploadedFile($caminho, 'programa.exe', null, null, true)),
        );
    }

    public function test_pdf_de_verdade_passa(): void
    {
        $caminho = tempnam(sys_get_temp_dir(), 'mid').'.pdf';
        file_put_contents($caminho, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF");

        $this->assertNull($this->erroDe(new UploadedFile($caminho, 'material.pdf', null, null, true)));
    }

    public function test_o_campo_so_aceita_a_extensao_que_ele_pede(): void
    {
        $caminho = tempnam(sys_get_temp_dir(), 'mid').'.pdf';
        file_put_contents($caminho, "%PDF-1.4\n%%EOF");
        $pdf = new UploadedFile($caminho, 'material.pdf', null, null, true);

        $this->assertNull($this->erroDe($pdf, ['pdf']));
        $this->assertStringContainsString('.epub', (string) $this->erroDe($pdf, ['epub']));
        $this->assertStringContainsString(
            'não uma imagem',
            (string) $this->erroDe($this->imagem('webp', 400, 300), ['pdf']),
        );
    }

    // ── metadados ───────────────────────────────────────────────────

    public function test_metadados_saem_do_arquivo_gravado(): void
    {
        $arquivo = $this->imagem('webp', 640, 480);
        Storage::disk('local')->put('midia/foto.webp', file_get_contents($arquivo->getRealPath()));

        $dados = Media::metadadosDe('midia/foto.webp');

        $this->assertSame('image/webp', $dados['mime_type']);
        $this->assertSame(640, $dados['width']);
        $this->assertSame(480, $dados['height']);
        $this->assertGreaterThan(0, $dados['size']);
    }

    public function test_documento_nao_ganha_dimensoes(): void
    {
        Storage::disk('local')->put('midia/material.pdf', "%PDF-1.4\n%%EOF");

        $dados = Media::metadadosDe('midia/material.pdf');

        $this->assertNull($dados['width']);
        $this->assertNull($dados['height']);
    }

    // ── permissoes ──────────────────────────────────────────────────

    public function test_editor_envia_e_edita_mas_nao_exclui(): void
    {
        $editor = $this->usuario(UserRole::Editor);
        $media = Media::factory()->create();

        $this->actingAs($editor);

        $this->assertTrue($editor->can('create', Media::class));
        $this->assertTrue($editor->can('update', $media));
        $this->assertFalse($editor->can('delete', $media));
    }

    public function test_administrador_exclui_e_o_arquivo_some_do_disco(): void
    {
        $admin = $this->usuario(UserRole::Administrator);

        Storage::disk('local')->put('midia/teste.webp', 'conteudo');
        $media = Media::factory()->create(['path' => 'midia/teste.webp']);

        $this->assertTrue($admin->can('delete', $media));

        $media->delete();

        $this->assertFalse(Storage::disk('local')->exists('midia/teste.webp'));
    }

    public function test_editor_alcanca_a_biblioteca(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor))
            ->get(MediaResource::getUrl('index'))
            ->assertOk();
    }

    // ── o arquivo entra pela tela do item ───────────────────────────

    /** O que o teste do Livewire sabe enviar: arquivo de verdade, com nome. */
    private function envio(UploadedFile $arquivo): File
    {
        return UploadedFile::fake()->createWithContent(
            $arquivo->getClientOriginalName(),
            (string) file_get_contents((string) $arquivo->getRealPath()),
        );
    }

    private function criarLivroComImagem(string $titulo): EducationItem
    {
        Livewire::withQueryParams(['tipo' => EducationType::Livro->value])
            ->test(CreateEducationItem::class)
            ->fillForm([
                'title' => $titulo,
                'image_id' => [$this->envio($this->imagem('webp', 800, 600))],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        return EducationItem::where('title', $titulo)->sole();
    }

    public function test_envio_na_tela_do_item_entra_na_biblioteca(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $item = $this->criarLivroComImagem('Livro novo');
        $media = Media::sole();

        $this->assertSame($media->getKey(), $item->image_id);
        $this->assertSame('foto.webp', $media->original_name);
        $this->assertSame(800, $media->width);
        $this->assertTrue(Storage::disk('local')->exists($media->path));
    }

    public function test_salvar_o_item_de_novo_nao_duplica_o_registro(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $item = $this->criarLivroComImagem('Livro novo');
        $media = Media::sole();

        Livewire::test(EditEducationItem::class, ['record' => $item->getKey()])
            ->fillForm(['title' => 'Livro renomeado'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(1, Media::count());
        $this->assertSame($media->getKey(), $item->fresh()->image_id);
    }

    // ── substituir vale para todos de uma vez ───────────────────────

    private function capa(): Media
    {
        $arquivo = $this->imagem('webp', 400, 200);
        $caminho = 'midia/capa-'.uniqid().'.webp';
        Storage::disk('local')->put($caminho, file_get_contents($arquivo->getRealPath()));

        return Media::factory()->create([
            'path' => $caminho,
            'name' => 'Capa',
            'alt' => 'Uma capa',
            'width' => 400,
            'height' => 200,
        ]);
    }

    public function test_substituir_troca_o_arquivo_em_todos_os_lugares(): void
    {
        $this->actingAs($this->usuario(UserRole::Administrator));

        $media = $this->capa();
        $antigo = $media->path;

        $post = Post::factory()->create(['image_id' => $media->getKey()]);
        $item = EducationItem::factory()->create(['image_id' => $media->getKey()]);

        Livewire::test(EditMedia::class, ['record' => $media->getKey()])
            ->callAction('substituir', ['path' => [$this->envio($this->imagem('webp', 1000, 500))]]);

        $novo = $media->fresh();

        // Mesmo registro: id, nome e alt ficam; o arquivo servido muda.
        $this->assertSame($media->getKey(), $novo->getKey());
        $this->assertSame('Capa', $novo->name);
        $this->assertSame('Uma capa', $novo->alt);
        $this->assertSame(1000, $novo->width);
        $this->assertSame(500, $novo->height);

        $this->assertNotSame($antigo, $novo->path);
        $this->assertFalse(Storage::disk('local')->exists($antigo));
        $this->assertTrue(Storage::disk('local')->exists($novo->path));

        // Ninguém precisou abrir item por item.
        $this->assertSame($novo->path, $post->fresh()->imagem->path);
        $this->assertSame($novo->path, $item->fresh()->imagem->path);
    }

    // ── excluir arquivo em uso ──────────────────────────────────────

    public function test_o_banco_recusa_apagar_midia_em_uso(): void
    {
        $media = Media::factory()->create();
        Post::factory()->create(['image_id' => $media->getKey()]);

        $this->expectException(QueryException::class);

        $media->delete();
    }

    public function test_a_tela_recusa_excluir_e_diz_onde_o_arquivo_esta(): void
    {
        $this->actingAs($this->usuario(UserRole::Administrator));

        $media = Media::factory()->create();
        Post::factory()->create(['title' => 'Pré-eclâmpsia', 'image_id' => $media->getKey()]);

        Livewire::test(EditMedia::class, ['record' => $media->getKey()])
            ->callAction('delete')
            ->assertNotified('Arquivo em uso');

        $this->assertModelExists($media);
    }

    public function test_item_na_lixeira_ainda_conta_como_uso(): void
    {
        $media = Media::factory()->create();
        $post = Post::factory()->create(['title' => 'Na lixeira', 'image_id' => $media->getKey()]);
        $post->delete();

        $this->assertSame(['Blog · Na lixeira (imagem)'], $media->usos());
    }

    public function test_excluir_midia_sem_uso_funciona_pela_tela(): void
    {
        $this->actingAs($this->usuario(UserRole::Administrator));

        Storage::disk('local')->put('midia/solto.webp', 'conteudo');
        $media = Media::factory()->create(['path' => 'midia/solto.webp']);

        Livewire::test(EditMedia::class, ['record' => $media->getKey()])
            ->callAction('delete');

        $this->assertModelMissing($media);
        $this->assertFalse(Storage::disk('local')->exists('midia/solto.webp'));
    }

    // ── usado em ────────────────────────────────────────────────────

    public function test_usos_lista_post_item_de_educacao_e_imagem_de_seo(): void
    {
        $media = Media::factory()->create();

        Post::factory()->create(['title' => 'Pré-eclâmpsia', 'image_id' => $media->getKey()]);
        EducationItem::factory()->create(['title' => 'Guia do parto', 'seo_image_id' => $media->getKey()]);
        Configuracao::instancia()->update(['seo_image_id' => $media->getKey()]);

        $this->assertSame([
            'Blog · Pré-eclâmpsia (imagem)',
            'Educação · Guia do parto (imagem de SEO)',
            'Configurações · geral (imagem de compartilhamento padrão)',
        ], $media->usos());
    }

    public function test_arquivo_sem_uso_nao_lista_nada(): void
    {
        $this->assertSame([], Media::factory()->create()->usos());
    }

    // ── imagem de SEO: envio direto ─────────────────────────────────

    public function test_imagem_de_seo_do_item_e_envio_direto_e_entra_na_biblioteca(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::withQueryParams(['tipo' => EducationType::Livro->value])
            ->test(CreateEducationItem::class)
            ->fillForm([
                'title' => 'Livro com SEO',
                'seo_image_id' => [$this->envio($this->imagem('webp', 600, 400))],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $media = Media::sole();

        $this->assertSame($media->getKey(), EducationItem::sole()->seo_image_id);
        $this->assertSame('foto.webp', $media->original_name);
        $this->assertTrue(Storage::disk('local')->exists($media->path));
    }

    public function test_imagem_de_seo_das_configuracoes_e_envio_direto(): void
    {
        $this->actingAs($this->usuario(UserRole::Administrator));

        Livewire::test(Configuracoes::class)
            ->fillForm([
                'seo_title' => 'Dra. Marina Mariz',
                'seo_description' => 'Ginecologia e obstetrícia em Belo Horizonte.',
                'seo_image_id' => [$this->envio($this->imagem('webp', 600, 400))],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(Media::sole()->getKey(), Configuracao::instancia()->seo_image_id);

        // A tela reabre com a imagem já enviada, sem estourar na hidratação.
        Livewire::test(Configuracoes::class)->assertOk();
    }

    // ── imagem colada no corpo do post ──────────────────────────────

    /** JSON do TipTap com a imagem no meio do texto: o vínculo é o id. */
    private function corpoCom(Media $imagem): array
    {
        return [
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Olhe a foto:']]],
                ['type' => 'image', 'attrs' => ['id' => (string) $imagem->getKey()]],
            ],
        ];
    }

    public function test_imagem_no_corpo_conta_como_uso_e_a_tela_recusa_excluir(): void
    {
        $this->actingAs($this->usuario(UserRole::Administrator));

        $imagem = $this->capa();
        Post::factory()->create(['title' => 'Parto humanizado', 'content' => $this->corpoCom($imagem)]);

        $this->assertSame(['Blog · Parto humanizado (imagem no corpo)'], $imagem->usos());

        Livewire::test(EditMedia::class, ['record' => $imagem->getKey()])
            ->callAction('delete')
            ->assertNotified('Arquivo em uso');

        $this->assertModelExists($imagem);
    }

    // ── ciclo de vida do arquivo ────────────────────────────────────

    public function test_rascunho_nunca_publicado_leva_os_arquivos_junto(): void
    {
        $capa = $this->capa();
        $post = Post::factory()->create(['image_id' => $capa->getKey()]);

        $post->forceDelete();

        $this->assertModelMissing($capa);
        $this->assertFalse(Storage::disk('local')->exists($capa->path));
    }

    public function test_imagem_no_corpo_de_rascunho_nunca_publicado_tambem_sai(): void
    {
        $imagem = $this->capa();
        $post = Post::factory()->create(['content' => $this->corpoCom($imagem)]);

        $post->forceDelete();

        $this->assertModelMissing($imagem);
        $this->assertFalse(Storage::disk('local')->exists($imagem->path));
    }

    public function test_post_que_ja_foi_publicado_mantem_os_arquivos(): void
    {
        $capa = $this->capa();
        $post = Post::factory()->publicado()->create(['image_id' => $capa->getKey()]);

        // Voltou para rascunho: o status esquece, o marco não.
        $post->update(['status' => ContentStatus::Rascunho]);
        $this->assertNotNull($post->fresh()->publicado_pela_primeira_vez_em);

        $post->forceDelete();

        $this->assertModelExists($capa);
        $this->assertTrue(Storage::disk('local')->exists($capa->path));
    }

    public function test_arquivo_de_dois_conteudos_nao_some_quando_um_deles_e_excluido(): void
    {
        $capa = $this->capa();
        $post = Post::factory()->create(['image_id' => $capa->getKey()]);
        EducationItem::factory()->create(['image_id' => $capa->getKey()]);

        $post->forceDelete();

        $this->assertModelExists($capa);
        $this->assertTrue(Storage::disk('local')->exists($capa->path));
    }

    public function test_lixeira_nao_apaga_arquivo_e_restaurar_devolve_tudo(): void
    {
        $capa = $this->capa();
        $post = Post::factory()->create(['image_id' => $capa->getKey()]);

        $post->delete();

        $this->assertModelExists($capa);
        $this->assertTrue(Storage::disk('local')->exists($capa->path));

        $post->restore();

        $this->assertSame($capa->getKey(), $post->fresh()->image_id);
        $this->assertTrue(Storage::disk('local')->exists($capa->fresh()->path));
    }

    public function test_item_de_educacao_segue_a_mesma_regra(): void
    {
        $rascunho = EducationItem::factory()->create(['image_id' => $this->capa()->getKey()]);
        $publicado = EducationItem::factory()->create([
            'status' => ContentStatus::Publicado,
            'external_url' => 'https://exemplo.com/livro',
            'image_id' => $this->capa()->getKey(),
        ]);

        [$daqui, $dali] = [$rascunho->imagem, $publicado->imagem];

        $rascunho->forceDelete();
        $publicado->forceDelete();

        $this->assertModelMissing($daqui);
        $this->assertModelExists($dali);
    }
}
