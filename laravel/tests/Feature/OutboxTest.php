<?php

namespace Tests\Feature;

use App\Mail\AvisoDeFormulario;
use App\Models\Media;
use App\Models\Mensagem;
use App\Models\OutboxJob;
use App\Support\Outbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

/**
 * Outbox transacional e o comando que entrega o e-mail de aviso (doc 08).
 */
class OutboxTest extends TestCase
{
    use RefreshDatabase;

    private const TEXTO = 'Tenho enxaqueca desde os catorze anos.';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config([
            'pessoas.ip_hash_key' => 'chave-de-teste',
            'pessoas.email_equipe' => 'contato@dramarinamariz.com.br',
        ]);
    }

    private function enviaContato(): Mensagem
    {
        $this->call('POST', '/formularios/contato', [
            'submission_id' => (string) Str::uuid(),
            'nome' => 'Ana Souza',
            'email' => 'ana@example.com',
            'whatsapp' => '(31) 99999-0000',
            'texto' => self::TEXTO,
            'ciencia_politica' => '1',
        ], [], [], ['HTTP_ACCEPT' => 'application/json'])->assertOk();

        return Mensagem::sole();
    }

    /** SMTP fora do ar nas `$falhas` primeiras tentativas, de pe depois. */
    private function smtpQueFalha(int $falhas): void
    {
        Mail::shouldReceive('to')->andReturnUsing(function () use (&$falhas) {
            if ($falhas-- > 0) {
                // Mensagem crua com o corpo do formulario dentro: e o que o
                // painel nunca pode acabar guardando.
                throw new \RuntimeException('SMTP recusou: '.self::TEXTO, 421);
            }

            return Mockery::mock(['send' => null]);
        });
    }

    // ── entrega ─────────────────────────────────────────────────────

    public function test_comando_entrega_o_email_e_marca_entregue(): void
    {
        Mail::fake();

        $this->enviaContato();

        $this->artisan('outbox:processar')->assertSuccessful();

        $this->assertSame(1, OutboxJob::where('status', OutboxJob::ENTREGUE)->count());
        $this->assertSame(0, OutboxJob::where('status', OutboxJob::PENDENTE)->count());
        $this->assertNotNull(OutboxJob::sole()->entregue_em);

        Mail::assertSent(AvisoDeFormulario::class, fn (AvisoDeFormulario $mail): bool => $mail->hasTo('contato@dramarinamariz.com.br')
            && $mail->hasReplyTo('ana@example.com'));
    }

    public function test_email_de_aviso_leva_todos_os_dados_da_mensagem(): void
    {
        Mail::fake();

        $this->enviaContato();
        $this->artisan('outbox:processar');

        Mail::assertSent(AvisoDeFormulario::class, function (AvisoDeFormulario $mail): bool {
            $corpo = $mail->render();

            return str_contains($corpo, self::TEXTO)
                && str_contains($corpo, 'Ana Souza')
                && str_contains($corpo, 'ana@example.com')
                && str_contains($corpo, '(31) 99999-0000');
        });
    }

    public function test_lead_leva_o_link_de_tres_dias_no_email_da_equipe(): void
    {
        Mail::fake();

        Storage::disk('local')->put('midia/guia.pdf', '%PDF-1.4');
        $material = Media::factory()->create(['path' => 'midia/guia.pdf', 'name' => 'Guia do pré-natal']);

        $this->call('POST', '/formularios/material', [
            'submission_id' => (string) Str::uuid(),
            'nome' => 'Ana Souza',
            'email' => 'ana@example.com',
            'media_id' => $material->getKey(),
            'ciencia_politica' => '1',
        ], [], [], ['HTTP_ACCEPT' => 'application/json'])->assertOk();

        $this->artisan('outbox:processar');

        Mail::assertSent(AvisoDeFormulario::class, fn (AvisoDeFormulario $mail): bool => str_contains((string) $mail->linkDeDownload, '/download/'.$material->getKey()));
    }

    // ── falha ───────────────────────────────────────────────────────

    public function test_falha_do_smtp_preserva_o_registro_e_deixa_pendente_para_nova_tentativa(): void
    {
        $this->smtpQueFalha(1);

        $mensagem = $this->enviaContato();

        $this->artisan('outbox:processar')->assertSuccessful();

        $this->assertSame(1, Mensagem::count());
        $this->assertSame($mensagem->texto, Mensagem::sole()->texto);

        $item = OutboxJob::sole();

        $this->assertSame(OutboxJob::PENDENTE, $item->status);
        $this->assertSame(1, $item->tentativas);
        $this->assertNotNull($item->proxima_tentativa_em);
        $this->assertStringNotContainsString(self::TEXTO, (string) $item->ultimo_erro);
        $this->assertSame('RuntimeException #421', $item->ultimo_erro);
    }

    public function test_pendencia_volta_a_sair_quando_o_smtp_normaliza(): void
    {
        $this->smtpQueFalha(1);

        $this->enviaContato();
        $this->artisan('outbox:processar');

        $this->assertSame(OutboxJob::PENDENTE, OutboxJob::sole()->status);

        $this->travel(10)->minutes();
        $this->artisan('outbox:processar');

        $item = OutboxJob::sole();
        $this->assertSame(OutboxJob::ENTREGUE, $item->status);
        $this->assertNull($item->ultimo_erro);
        $this->assertNotNull($item->entregue_em);
    }

    public function test_item_so_e_tentado_de_novo_depois_do_backoff(): void
    {
        $this->smtpQueFalha(99);

        $this->enviaContato();
        $this->artisan('outbox:processar');
        // Segunda execucao imediata do cron nao repete o item.
        $this->artisan('outbox:processar');

        $this->assertSame(1, OutboxJob::sole()->tentativas);
    }

    public function test_depois_do_ultimo_backoff_o_item_vira_falhou_e_para(): void
    {
        $this->smtpQueFalha(99);
        config(['pessoas.outbox.backoff' => [1]]);

        $this->enviaContato();

        $this->artisan('outbox:processar');
        $this->travel(10)->minutes();
        $this->artisan('outbox:processar');

        $item = OutboxJob::sole();
        $this->assertSame(OutboxJob::FALHOU, $item->status);
        $this->assertNull($item->proxima_tentativa_em);
    }

    // ── transacao ───────────────────────────────────────────────────

    public function test_se_o_banco_falhar_nada_e_gravado(): void
    {
        // A outbox e a ultima gravacao da transacao: se ela estourar, a
        // mensagem tambem nao pode sobrar.
        DB::listen(function ($query): void {
            if (str_contains($query->sql, 'insert into `outbox_jobs`')) {
                throw new \RuntimeException('banco caiu');
            }
        });

        try {
            $this->enviaContato();
        } catch (\Throwable) {
            // O visitante veria a tela de erro com os campos preservados.
        }

        $this->assertSame(0, Mensagem::count());
        $this->assertSame(0, OutboxJob::count());
    }

    public function test_erro_curto_nao_carrega_a_mensagem_da_excecao(): void
    {
        $erro = Outbox::erroCurto(new \RuntimeException('senha=abc123 corpo='.self::TEXTO, 7));

        $this->assertSame('RuntimeException #7', $erro);
    }

    public function test_registro_apagado_do_painel_nao_trava_a_fila(): void
    {
        Mail::fake();

        $mensagem = $this->enviaContato();
        $mensagem->delete();

        $this->artisan('outbox:processar')->assertSuccessful();

        $this->assertSame(1, OutboxJob::where('status', OutboxJob::FALHOU)->count());
    }
}
