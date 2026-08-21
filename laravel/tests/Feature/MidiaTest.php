<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use App\Models\User;
use App\Rules\ArquivoDeMidia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
        };

        imagedestroy($tela);

        return new UploadedFile($caminho, "foto.{$formato}", null, null, true);
    }

    private function erroDe(UploadedFile $arquivo): ?string
    {
        $validator = Validator::make(
            ['arquivo' => $arquivo],
            ['arquivo' => [new ArquivoDeMidia]],
        );

        return $validator->errors()->first('arquivo') ?: null;
    }

    // ── validacao no servidor ────────────────────────────────────────

    public function test_webp_dentro_dos_limites_passa(): void
    {
        $this->assertNull($this->erroDe($this->imagem('webp', 1200, 800)));
    }

    public function test_imagem_que_nao_chegou_em_webp_e_recusada(): void
    {
        $this->assertStringContainsString('WebP', (string) $this->erroDe($this->imagem('jpeg', 800, 600)));
    }

    public function test_imagem_acima_de_1920px_e_recusada(): void
    {
        $this->assertStringContainsString('1920', (string) $this->erroDe($this->imagem('webp', 2400, 1000)));
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
}
