<?php

namespace Tests\Feature;

use App\Http\Controllers\DownloadProtegidoController;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadProtegidoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::disk('local')->put('midia/guia.pdf', '%PDF-1.4');
    }

    private function material(): Media
    {
        return Media::factory()->create([
            'path' => 'midia/guia.pdf',
            'original_name' => 'guia-do-pre-natal.pdf',
            'mime_type' => 'application/pdf',
        ]);
    }

    public function test_link_assinado_entrega_o_arquivo(): void
    {
        $resposta = $this->get(DownloadProtegidoController::linkPara($this->material()));

        $resposta->assertOk();
        $resposta->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('guia-do-pre-natal.pdf', $resposta->headers->get('content-disposition'));
    }

    public function test_link_sem_assinatura_e_recusado(): void
    {
        $this->get('/download/'.$this->material()->getKey())->assertForbidden();
    }

    public function test_link_vale_exatamente_tres_dias(): void
    {
        $expira = now()->addDays(DownloadProtegidoController::VALIDADE_EM_DIAS);
        $link = DownloadProtegidoController::linkPara($this->material());

        $this->travelTo($expira->copy()->subMinute());
        $this->get($link)->assertOk();

        $this->travelTo($expira->copy()->addMinute());
        $this->get($link)->assertForbidden();
    }

    public function test_arquivo_sumido_do_disco_responde_404(): void
    {
        $media = $this->material();
        Storage::disk('local')->delete('midia/guia.pdf');

        $this->get(DownloadProtegidoController::linkPara($media))->assertNotFound();
    }

    public function test_assinatura_adulterada_e_recusada(): void
    {
        $link = DownloadProtegidoController::linkPara($this->material());

        $this->get(substr($link, 0, -4).'aaaa')->assertForbidden();
    }
}
