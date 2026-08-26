<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\PostCta;
use App\Enums\UserRole;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Support\AnexosNaBiblioteca;
use App\Support\ExportaPost;
use Database\Seeders\BlogCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BlogTest extends TestCase
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

    private function admin(): User
    {
        return $this->usuario(UserRole::Administrator);
    }

    private function editor(): User
    {
        return $this->usuario(UserRole::Editor);
    }

    // ── papéis ──────────────────────────────────────────────────────

    public function test_editor_cria_edita_e_publica(): void
    {
        $editor = $this->editor();
        $post = Post::factory()->create();

        $this->actingAs($editor);

        $this->assertTrue($editor->can('create', Post::class));
        $this->assertTrue($editor->can('update', $post));

        $post->update(['status' => ContentStatus::Publicado, 'published_at' => now()->subMinute()]);

        $this->assertTrue($post->fresh()->estaNoAr());
    }

    public function test_editor_nao_arquiva_nem_desarquiva(): void
    {
        $editor = $this->editor();
        $post = Post::factory()->publicado()->create();

        $this->actingAs($editor);

        $this->assertFalse($editor->can('arquivar', $post));

        $this->expectExceptionMessage('Somente o Administrador arquiva ou desarquiva um post.');
        $post->update(['status' => ContentStatus::Arquivado]);
    }

    public function test_editor_nao_tira_post_do_arquivo(): void
    {
        $editor = $this->editor();
        $post = Post::factory()->arquivado()->create();

        $this->actingAs($editor);

        try {
            $post->update(['status' => ContentStatus::Publicado]);
            $this->fail('O editor não deveria conseguir desarquivar.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertSame(ContentStatus::Arquivado, $post->fresh()->status);
    }

    public function test_editor_nao_envia_a_lixeira_nem_exclui(): void
    {
        $editor = $this->editor();
        $post = Post::factory()->create();

        $this->assertFalse($editor->can('delete', $post));
        $this->assertFalse($editor->can('restore', $post));
        $this->assertFalse($editor->can('forceDelete', $post));
    }

    public function test_administrador_arquiva_desarquiva_e_exclui(): void
    {
        $admin = $this->admin();
        $post = Post::factory()->publicado()->create();

        $this->actingAs($admin);

        $post->update(['status' => ContentStatus::Arquivado]);
        $this->assertSame(ContentStatus::Arquivado, $post->fresh()->status);

        $post->update(['status' => ContentStatus::Rascunho]);
        $this->assertSame(ContentStatus::Rascunho, $post->fresh()->status);

        $this->assertTrue($admin->can('delete', $post));
        $this->assertTrue($admin->can('restore', $post));
        $this->assertTrue($admin->can('forceDelete', $post));
    }

    public function test_categorias_sao_so_do_administrador(): void
    {
        $categoria = Category::factory()->create();

        $this->assertTrue($this->admin()->can('viewAny', Category::class));
        $this->assertFalse($this->editor()->can('update', $categoria));

        $this->actingAs($this->editor())
            ->get(CategoryResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->get(CategoryResource::getUrl('index'))
            ->assertOk();
    }

    public function test_editor_alcanca_a_lista_de_posts(): void
    {
        $this->actingAs($this->editor())
            ->get(PostResource::getUrl('index'))
            ->assertOk();
    }

    /**
     * Clicar no post abre o editor, nunca uma tela de leitura: o painel e
     * para escrever. Visualizar e ver a pagina publica, no site.
     */
    public function test_clicar_no_post_abre_a_edicao(): void
    {
        $post = Post::factory()->create();

        $this->assertArrayNotHasKey('view', PostResource::getPages());

        $this->actingAs($this->editor())
            ->get(PostResource::getUrl('edit', ['record' => $post]))
            ->assertOk();
    }

    public function test_ver_no_site_so_aparece_para_post_no_ar(): void
    {
        $noAr = Post::factory()->publicado()->create();
        $rascunho = Post::factory()->create();

        $this->actingAs($this->editor());

        Livewire::test(ListPosts::class)
            ->assertTableActionVisible('verNoSite', $noAr)
            ->assertTableActionHidden('verNoSite', $rascunho);
    }

    public function test_a_tabela_esconde_do_editor_o_que_e_do_administrador(): void
    {
        $post = Post::factory()->publicado()->create();

        $this->actingAs($this->editor());

        Livewire::test(ListPosts::class)
            ->assertTableActionHidden('arquivar', $post)
            ->assertTableActionHidden('replicate', $post)
            ->assertTableActionHidden('delete', $post)
            ->assertTableActionVisible('baixarPdf', $post);

        $this->actingAs($this->admin());

        Livewire::test(ListPosts::class)
            ->assertTableActionVisible('arquivar', $post)
            ->assertTableActionVisible('replicate', $post)
            ->assertTableActionVisible('delete', $post);
    }

    // ── recorte público ─────────────────────────────────────────────

    public function test_post_agendado_fica_fora_do_site_ate_a_data_chegar(): void
    {
        $post = Post::factory()->agendado()->create();

        $this->assertTrue($post->estaAgendado());
        $this->assertSame('Agendado', $post->rotuloDeEstado());
        $this->assertFalse(Post::noAr()->whereKey($post->getKey())->exists());

        // Nada roda no horário: a própria consulta pública é quem decide.
        $this->travelTo(now()->addMonth());

        $this->assertTrue(Post::noAr()->whereKey($post->getKey())->exists());
        $this->assertSame('Publicado', $post->fresh()->rotuloDeEstado());
    }

    public function test_rascunho_e_arquivado_nunca_aparecem_no_site(): void
    {
        Post::factory()->create();
        Post::factory()->arquivado()->create(['published_at' => now()->subDay()]);
        $publicado = Post::factory()->publicado()->create();

        $this->assertSame([$publicado->getKey()], Post::noAr()->pluck('id')->all());
    }

    public function test_agendado_nao_e_um_quarto_status_no_banco(): void
    {
        $post = Post::factory()->agendado()->create();

        $this->assertSame(
            ContentStatus::Publicado->value,
            $post->fresh()->getRawOriginal('status'),
        );
    }

    // ── categorias ──────────────────────────────────────────────────

    public function test_excluir_categoria_move_os_posts_para_sem_categoria(): void
    {
        $categoria = Category::factory()->create();
        $post = Post::factory()->publicado()->create(['category_id' => $categoria->getKey()]);

        $categoria->delete();

        $post->refresh();

        $this->assertTrue($post->exists);
        $this->assertNull($post->category_id);
        $this->assertSame('Sem categoria', $post->nomeDaCategoria());
    }

    public function test_renomear_categoria_nao_mexe_no_slug(): void
    {
        $categoria = Category::factory()->create(['name' => 'Artigos', 'slug' => 'artigos']);

        $categoria->update(['name' => 'Artigos e ensaios']);

        $this->assertSame('artigos', $categoria->fresh()->slug);
    }

    public function test_seeder_cria_as_quatro_categorias_iniciais(): void
    {
        $this->seed(BlogCategorySeeder::class);
        $this->seed(BlogCategorySeeder::class);

        $this->assertSame(
            ['Artigos', 'Episódios', 'Materiais', 'Relatos de Parto'],
            Category::orderBy('position')->pluck('name')->all(),
        );
    }

    /** O seeder do banco precisa chamar o do blog, senao ninguem cria as categorias. */
    public function test_o_seeder_do_banco_traz_as_categorias_do_blog(): void
    {
        $this->seed();

        $this->assertSame(4, Category::count());
    }

    public function test_a_lista_conta_os_posts_sem_categoria(): void
    {
        Post::factory()->count(2)->create(['category_id' => null]);
        Post::factory()->create(['category_id' => Category::factory()->create()->getKey()]);

        $this->actingAs($this->admin());

        Livewire::test(ListPosts::class)
            ->assertSee('2 posts sem categoria');
    }

    public function test_o_filtro_de_categoria_isola_os_sem_categoria(): void
    {
        $sem = Post::factory()->create(['category_id' => null]);
        $com = Post::factory()->create(['category_id' => Category::factory()->create()->getKey()]);

        $this->actingAs($this->admin());

        Livewire::test(ListPosts::class)
            ->filterTable('categoria', 'sem')
            ->assertCanSeeTableRecords([$sem])
            ->assertCanNotSeeTableRecords([$com]);
    }

    public function test_o_filtro_de_status_comeca_escondendo_os_arquivados(): void
    {
        $arquivado = Post::factory()->arquivado()->create();
        $rascunho = Post::factory()->create();

        $this->actingAs($this->admin());

        Livewire::test(ListPosts::class)
            ->assertCanSeeTableRecords([$rascunho])
            ->assertCanNotSeeTableRecords([$arquivado])
            ->filterTable('status', [ContentStatus::Arquivado->value])
            ->assertCanSeeTableRecords([$arquivado]);
    }

    // ── slug ────────────────────────────────────────────────────────

    public function test_slug_e_sugerido_do_titulo(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreatePost::class)
            ->fillForm(['title' => 'Pré-eclâmpsia: mitos e verdades'])
            ->assertFormSet(['slug' => 'pre-eclampsia-mitos-e-verdades']);
    }

    public function test_slug_em_branco_sai_do_titulo_no_servidor(): void
    {
        $post = Post::create(['title' => 'Gestação de alto risco']);

        $this->assertSame('gestacao-de-alto-risco', $post->slug);
    }

    public function test_slug_repetido_da_erro_de_validacao(): void
    {
        Post::factory()->create(['slug' => 'pre-eclampsia']);

        $this->actingAs($this->admin());

        Livewire::test(CreatePost::class)
            ->fillForm(['title' => 'Outro post', 'slug' => 'pre-eclampsia'])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_trocar_o_slug_de_post_publicado_mostra_o_aviso(): void
    {
        $post = Post::factory()->publicado()->create(['slug' => 'pre-eclampsia']);

        $this->actingAs($this->admin());

        Livewire::test(EditPost::class, ['record' => $post->getKey()])
            ->assertDontSee('passa a responder 404')
            ->fillForm(['slug' => 'outro-endereco'])
            ->assertSee('passa a responder 404');
    }

    // ── obrigatório para publicar, não para salvar ──────────────────

    public function test_rascunho_salva_so_com_o_titulo(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreatePost::class)
            ->fillForm(['title' => 'Ideia pela metade', 'status' => ContentStatus::Rascunho->value])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('posts', ['title' => 'Ideia pela metade']);
    }

    public function test_publicar_exige_os_campos_com_asterisco(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreatePost::class)
            ->fillForm(['title' => 'Vai ao ar sem nada', 'status' => ContentStatus::Publicado->value])
            ->call('create')
            ->assertHasFormErrors(['description', 'image_id', 'content', 'published_at', 'closing_cta']);

        $this->assertDatabaseMissing('posts', ['title' => 'Vai ao ar sem nada']);
    }

    public function test_o_botao_publicar_carimba_a_data_quando_ninguem_preencheu(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Pré-natal sem susto',
                'description' => 'Descrição.',
                'content' => '<p>Texto.</p>',
                'image_id' => [$this->imagemNaBiblioteca()->getKey()],
                'closing_cta' => PostCta::Newsletter->value,
            ])
            ->call('publicarRegistro')
            ->assertHasNoFormErrors();

        $post = Post::where('title', 'Pré-natal sem susto')->sole();

        $this->assertTrue($post->estaPublicado());
        $this->assertNotNull($post->published_at);
    }

    public function test_publicar_com_cta_de_material_exige_descricao_e_arquivo(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Guia do pré-natal',
                'status' => ContentStatus::Publicado->value,
                'closing_cta' => PostCta::Material->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['material_description', 'material_media_id']);
    }

    public function test_publicar_com_cta_de_podcast_exige_o_link(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Episódio 12',
                'status' => ContentStatus::Publicado->value,
                'closing_cta' => PostCta::Podcast->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['episode_url']);
    }

    public function test_cta_de_newsletter_nao_pede_campo_nenhum(): void
    {
        $post = Post::factory()->publicado()->create([
            'closing_cta' => PostCta::Newsletter,
            'material_description' => 'lixo que veio do Livewire',
            'episode_url' => 'https://youtube.com/x',
        ]);

        $post->refresh();

        $this->assertNull($post->material_description);
        $this->assertNull($post->episode_url);
    }

    // ── posts relacionados ──────────────────────────────────────────

    public function test_relacionado_arquivado_ou_na_lixeira_some_da_lista(): void
    {
        $post = Post::factory()->publicado()->create();
        $bom = Post::factory()->publicado()->create();
        $arquivado = Post::factory()->arquivado()->create();
        $lixeira = Post::factory()->publicado()->create();
        $rascunho = Post::factory()->create();
        $agendado = Post::factory()->agendado()->create();

        $post->relacionados()->attach([
            $bom->getKey(), $arquivado->getKey(), $lixeira->getKey(),
            $rascunho->getKey(), $agendado->getKey(),
        ]);

        $this->actingAs($this->admin());
        $lixeira->delete();

        $this->assertSame([$bom->getKey()], $post->relacionadosNoAr()->pluck('posts.id')->all());

        // No painel a lista traz rascunho e arquivado, para poder escolher
        // antes de publicar; só a lixeira some, por causa do SoftDeletes.
        $this->assertCount(4, $post->relacionados()->get());
    }

    public function test_relacionados_param_em_quatro(): void
    {
        $post = Post::factory()->create();
        $outros = Post::factory()->count(5)->create()->pluck('id')->all();

        $this->actingAs($this->admin());

        Livewire::test(EditPost::class, ['record' => $post->getKey()])
            ->fillForm(['relacionados' => $outros])
            ->call('save')
            ->assertHasFormErrors(['relacionados']);
    }

    // ── conteúdo rico ───────────────────────────────────────────────

    public function test_anexo_colado_no_editor_entra_na_biblioteca(): void
    {
        $this->actingAs($this->admin());

        // Em teste o Livewire guarda o upload em `tmp-for-tests/livewire-tmp`.
        Storage::fake('tmp-for-tests');
        $fake = UploadedFile::fake()->image('colada.png');
        $nome = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($fake);
        $fake->storeAs('livewire-tmp', $nome, 'tmp-for-tests');

        $temporario = TemporaryUploadedFile::createFromLivewire($nome);

        $id = (new AnexosNaBiblioteca)->saveUploadedFileAttachment($temporario);

        $this->assertDatabaseHas('media', ['id' => $id, 'original_name' => 'colada.png']);
        $this->assertTrue(Storage::disk('local')->exists(Media::find($id)->path));
    }

    public function test_id_de_anexo_que_nao_esta_na_biblioteca_nao_vira_url(): void
    {
        $this->assertNull((new AnexosNaBiblioteca)->getFileAttachmentUrl('midia/../../.env'));
        $this->assertNull((new AnexosNaBiblioteca)->getFileAttachmentUrl('999999'));
    }

    public function test_conteudo_rico_sai_sanitizado(): void
    {
        $post = Post::factory()->create([
            'content' => [
                'type' => 'doc',
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [[
                        'type' => 'text',
                        'text' => 'oi',
                        'marks' => [['type' => 'link', 'attrs' => ['href' => 'javascript:alert(1)']]],
                    ]],
                ]],
            ],
        ]);

        $html = $post->renderRichContent('content');

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('oi', $html);
    }

    // ── exportação ──────────────────────────────────────────────────

    /** Imagem WebP de verdade no disco privado — é o que a biblioteca guarda. */
    private function imagemNaBiblioteca(): Media
    {
        $tela = imagecreatetruecolor(400, 200);
        $arquivo = tempnam(sys_get_temp_dir(), 'img').'.webp';
        imagewebp($tela, $arquivo, 82);
        imagedestroy($tela);

        $caminho = 'midia/capa.webp';
        Storage::disk('local')->put($caminho, file_get_contents($arquivo));
        unlink($arquivo);

        return Media::factory()->create(['path' => $caminho, 'alt' => 'Capa do post']);
    }

    private function postCompleto(): Post
    {
        $categoria = Category::factory()->create(['name' => 'Artigos']);

        return Post::factory()->publicado()->create([
            'image_id' => $this->imagemNaBiblioteca()->getKey(),
            'title' => 'Pré-eclâmpsia: mitos e verdades',
            'slug' => 'pre-eclampsia-mitos-e-verdades',
            'category_id' => $categoria->getKey(),
            'description' => 'O que a gestante precisa saber sobre pressão alta.',
            'published_at' => now()->setDate(2025, 1, 15)->setTime(12, 0),
            'seo_title' => 'SEGREDO DE SEO',
            'seo_description' => 'DESCRICAO DE SEO',
            'content' => [
                'type' => 'doc',
                'content' => [
                    ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Sintomas de atenção']]],
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Inchaço súbito e visão embaçada.']]],
                    ['type' => 'bulletList', 'content' => [
                        ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Pressão acima de 14 por 9']]]]],
                    ]],
                    ['type' => 'blockquote', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Toda gestante merece pré-natal.']]]]],
                    ['type' => 'paragraph', 'content' => [[
                        'type' => 'text',
                        'text' => 'Marque consulta',
                        'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://dramarinamariz.com.br/contato']]],
                    ]]],
                    ['type' => 'table', 'content' => [
                        ['type' => 'tableRow', 'content' => [
                            ['type' => 'tableHeader', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Semana']]]]],
                            ['type' => 'tableHeader', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Exame']]]]],
                        ]],
                        ['type' => 'tableRow', 'content' => [
                            ['type' => 'tableCell', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '20ª']]]]],
                            ['type' => 'tableCell', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Morfológico']]]]],
                        ]],
                    ]],
                    // Imagem que não está na biblioteca: vira texto legível.
                    ['type' => 'image', 'attrs' => ['id' => '999999']],
                ],
            ],
        ]);
    }

    private function textoDoDocx(string $caminho): string
    {
        $zip = new \ZipArchive;
        $zip->open($caminho);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        return html_entity_decode(strip_tags(str_replace('<', ' <', $xml)));
    }

    public function test_pdf_leva_o_post_e_nao_leva_dado_interno(): void
    {
        $post = $this->postCompleto();
        $this->actingAs($this->admin());

        // O HTML é a única entrada do Dompdf: o que não está aqui não
        // pode estar no PDF.
        $html = ExportaPost::html($post);

        foreach (['Pré-eclâmpsia', 'Artigos', 'Dra. Marina Mariz', '15/01/2025', 'pressão alta', 'Sintomas de atenção', '<li>', 'Pressão acima', '<blockquote>', 'Toda gestante merece', 'https://dramarinamariz.com.br/contato', 'Morfológico', '<table', 'data:image/webp;base64,', 'alt="Capa do post"', 'não substitui consulta médica', 'imagem indisponível'] as $esperado) {
            $this->assertStringContainsString($esperado, $html, "Faltou no PDF: {$esperado}");
        }

        foreach (['SEGREDO DE SEO', 'DESCRICAO DE SEO', 'seo_', 'publicado', 'status', 'data-id'] as $proibido) {
            $this->assertStringNotContainsString($proibido, $html, "Vazou no PDF: {$proibido}");
        }

        $resposta = ExportaPost::pdf($post);
        $arquivo = $resposta->getFile()->getPathname();

        $this->assertStringStartsWith('%PDF', file_get_contents($arquivo, length: 4));
        $this->assertGreaterThan(1000, filesize($arquivo));
        $this->assertStringContainsString('pre-eclampsia-mitos-e-verdades-2025-01-15.pdf', $resposta->headers->get('Content-Disposition'));
    }

    public function test_docx_leva_o_post_e_nao_leva_dado_interno(): void
    {
        $post = $this->postCompleto();
        $this->actingAs($this->admin());

        $resposta = ExportaPost::docx($post);
        $arquivo = tempnam(sys_get_temp_dir(), 'docx');
        copy($resposta->getFile()->getPathname(), $arquivo);

        $texto = $this->textoDoDocx($arquivo);

        foreach (['Pré-eclâmpsia', 'Artigos', 'Dra. Marina Mariz', '15/01/2025', 'Sintomas de atenção', 'Pressão acima', 'Toda gestante merece', 'Morfológico', 'não substitui consulta médica', 'imagem indisponível'] as $esperado) {
            $this->assertStringContainsString($esperado, $texto, "Faltou no DOCX: {$esperado}");
        }

        foreach (['SEGREDO DE SEO', 'DESCRICAO DE SEO'] as $proibido) {
            $this->assertStringNotContainsString($proibido, $texto, "Vazou no DOCX: {$proibido}");
        }

        $zip = new \ZipArchive;
        $zip->open($arquivo);
        $temImagem = $zip->locateName('word/media/section_image1.png') !== false;
        $zip->close();

        // Imagem da biblioteca (WebP) convertida e embutida no .docx.
        $this->assertTrue($temImagem, 'A imagem do post não entrou no DOCX.');

        unlink($arquivo);
    }

    public function test_o_nome_do_arquivo_usa_slug_e_data(): void
    {
        $post = $this->postCompleto();

        $this->assertSame('pre-eclampsia-mitos-e-verdades-2025-01-15.pdf', ExportaPost::nome($post, 'pdf'));
        $this->assertSame('pre-eclampsia-mitos-e-verdades-2025-01-15.docx', ExportaPost::nome($post, 'docx'));
    }

    public function test_o_temporario_e_apagado_depois_da_resposta(): void
    {
        $post = $this->postCompleto();
        $this->actingAs($this->admin());

        $resposta = ExportaPost::docx($post);
        $caminho = $resposta->getFile()->getPathname();

        $this->assertFileExists($caminho);
        $this->assertTrue($resposta->headers->has('Content-Disposition'));

        ob_start();
        $resposta->sendContent();
        ob_end_clean();

        $this->assertFileDoesNotExist($caminho);

        // O PHPWord embute a imagem no .docx; nenhum PNG solto fica para trás.
        $this->assertEmpty(glob(dirname($caminho).'/*.png'));
    }

    public function test_rascunho_tambem_exporta(): void
    {
        $post = Post::factory()->create(['title' => 'Ainda rascunho']);
        $this->actingAs($this->editor());

        $this->assertStringContainsString('Ainda rascunho', ExportaPost::html($post));
        $this->assertFileExists(ExportaPost::pdf($post)->getFile()->getPathname());
    }

    public function test_os_dois_papeis_baixam(): void
    {
        $post = $this->postCompleto();

        foreach ([$this->admin(), $this->editor()] as $usuario) {
            $this->actingAs($usuario);

            Livewire::test(EditPost::class, ['record' => $post->getKey()])
                ->assertActionVisible('baixarPdf')
                ->assertActionVisible('baixarWord');
        }
    }
}
