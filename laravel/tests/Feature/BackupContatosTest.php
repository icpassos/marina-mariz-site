<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Widgets\PessoasQueChegaram;
use App\Mail\RelatorioMensalDeBackups;
use App\Models\BackupDeContatos;
use App\Models\InscricaoNewsletter;
use App\Models\Lead;
use App\Models\LeadDownload;
use App\Models\Mensagem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Backup semanal dos contatos e o acompanhamento mensal por e-mail.
 */
class BackupContatosTest extends TestCase
{
    use RefreshDatabase;

    private string $pasta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pasta = storage_path('framework/testing/backups-'.uniqid());

        config([
            'pessoas.backup_contatos_path' => $this->pasta,
            'pessoas.email_equipe' => 'contato@dramarinamariz.com.br',
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->pasta);
        File::deleteDirectory(storage_path('app/private/backups-contatos'));

        parent::tearDown();
    }

    private function csv(string $tipo, ?string $data = null): string
    {
        return $this->pasta.'/'.$tipo.'-'.($data ?? now()->format('Y-m-d')).'.csv';
    }

    // ── conteudo ────────────────────────────────────────────────────

    public function test_gera_um_csv_por_tipo_com_o_conteudo_dos_registros(): void
    {
        Mensagem::factory()->create(['nome' => 'Ana Souza', 'email' => 'ana@example.com']);
        $lead = Lead::factory()->create(['nome' => 'Bruno Lima', 'email' => 'bruno@example.com']);
        LeadDownload::factory()->for($lead)->create(['origem' => 'Guia do pré-natal']);
        InscricaoNewsletter::factory()->create(['email' => 'carla@example.com']);

        $this->artisan('backup:contatos')->assertSuccessful();

        $mensagens = file_get_contents($this->csv('mensagens'));
        $this->assertStringContainsString('Ana Souza', $mensagens);
        $this->assertStringContainsString('ana@example.com', $mensagens);
        // Backup e copia de seguranca: o texto tem de estar la.
        $this->assertStringContainsString('submission_id', $mensagens);

        $leads = file_get_contents($this->csv('leads'));
        $this->assertStringContainsString('Bruno Lima', $leads);
        $this->assertStringContainsString('Guia do pré-natal', $leads);

        $this->assertStringContainsString('carla@example.com', file_get_contents($this->csv('newsletter')));

        // Nenhum IP em claro nem hash de IP sai no backup.
        $this->assertStringNotContainsString('ip_hash', $mensagens);
    }

    public function test_valor_que_viraria_formula_sai_neutralizado(): void
    {
        Mensagem::factory()->create(['nome' => '=SOMA(A1:A9)']);

        $this->artisan('backup:contatos');

        $this->assertStringContainsString("'=SOMA(A1:A9)", file_get_contents($this->csv('mensagens')));
    }

    public function test_arquivo_e_pasta_nascem_com_permissao_restrita(): void
    {
        Mensagem::factory()->create();

        $this->artisan('backup:contatos');

        $this->assertSame('0600', substr(sprintf('%o', fileperms($this->csv('mensagens'))), -4));
        $this->assertSame('0700', substr(sprintf('%o', fileperms($this->pasta)), -4));
    }

    // ── recorte ─────────────────────────────────────────────────────

    public function test_segunda_execucao_leva_so_o_que_chegou_depois(): void
    {
        Mensagem::factory()->create(['nome' => 'Ana Souza']);
        $this->artisan('backup:contatos');

        $this->travel(7)->days();
        Mensagem::factory()->create(['nome' => 'Bruno Lima']);
        $this->artisan('backup:contatos');

        $segundo = file_get_contents($this->csv('mensagens'));
        $this->assertStringContainsString('Bruno Lima', $segundo);
        $this->assertStringNotContainsString('Ana Souza', $segundo);
    }

    public function test_cron_parado_por_semanas_nao_deixa_buraco(): void
    {
        Mensagem::factory()->create(['nome' => 'Ana Souza']);
        $this->artisan('backup:contatos');

        // Duas semanas sem execucao, uma mensagem em cada.
        $this->travel(7)->days();
        Mensagem::factory()->create(['nome' => 'Bruno Lima']);

        $this->travel(7)->days();
        Mensagem::factory()->create(['nome' => 'Carla Dias']);

        $this->travel(1)->days();
        $this->artisan('backup:contatos');

        // O recorte sai do marco, nao de "ultimos 7 dias": as duas entram.
        $csv = file_get_contents($this->csv('mensagens'));
        $this->assertStringContainsString('Bruno Lima', $csv);
        $this->assertStringContainsString('Carla Dias', $csv);
        $this->assertSame(2, BackupDeContatos::latest('id')->first()->mensagens);
    }

    public function test_lead_antigo_com_download_novo_volta_para_o_backup(): void
    {
        $lead = Lead::factory()->create(['nome' => 'Bruno Lima']);
        LeadDownload::factory()->for($lead)->create(['origem' => 'Guia do pré-natal']);
        $this->artisan('backup:contatos');

        $this->travel(7)->days();
        LeadDownload::factory()->for($lead)->create(['origem' => 'E-book do sono']);
        $this->artisan('backup:contatos');

        $this->assertStringContainsString('E-book do sono', file_get_contents($this->csv('leads')));
    }

    public function test_opcao_tudo_gera_dump_completo(): void
    {
        Mensagem::factory()->create(['nome' => 'Ana Souza']);
        $this->artisan('backup:contatos');

        $this->travel(7)->days();
        Mensagem::factory()->create(['nome' => 'Bruno Lima']);
        $this->artisan('backup:contatos', ['--tudo' => true]);

        $csv = file_get_contents($this->csv('mensagens'));
        $this->assertStringContainsString('Ana Souza', $csv);
        $this->assertStringContainsString('Bruno Lima', $csv);
    }

    public function test_semana_sem_registro_nao_cria_arquivo_vazio(): void
    {
        $this->artisan('backup:contatos')->assertSuccessful();

        $this->assertFileDoesNotExist($this->csv('mensagens'));
        $this->assertFileDoesNotExist($this->csv('leads'));
        $this->assertFileDoesNotExist($this->csv('newsletter'));

        // O marco e gravado mesmo assim: cron rodou, so nao havia movimento.
        $this->assertSame(0, BackupDeContatos::sole()->total());
    }

    // ── destino ─────────────────────────────────────────────────────

    public function test_caminho_inexistente_cai_no_fallback_sem_estourar(): void
    {
        config(['pessoas.backup_contatos_path' => '/dev/null/nao-existe']);
        Mensagem::factory()->create(['nome' => 'Ana Souza']);

        $this->artisan('backup:contatos')->assertSuccessful();

        $fallback = storage_path('app/private/backups-contatos/mensagens-'.now()->format('Y-m-d').'.csv');
        $this->assertFileExists($fallback);
        $this->assertStringContainsString('Ana Souza', file_get_contents($fallback));
        $this->assertStringContainsString('backups-contatos', BackupDeContatos::sole()->caminho);
    }

    // ── painel ──────────────────────────────────────────────────────

    public function test_quadro_do_painel_alerta_quando_o_marco_esta_velho(): void
    {
        BackupDeContatos::create([
            'executado_em' => now()->subDays(BackupDeContatos::DIAS_ATE_O_ALERTA + 1),
            'caminho' => $this->pasta,
        ]);

        $resumo = PessoasQueChegaram::resumoDoBackup();

        $this->assertSame('danger', $resumo['cor']);
        $this->assertStringContainsString('cron', $resumo['descricao']);
    }

    public function test_quadro_do_painel_fica_verde_com_backup_recente(): void
    {
        BackupDeContatos::create([
            'executado_em' => now()->subDay(),
            'caminho' => $this->pasta,
            'mensagens' => 3,
        ]);

        $this->assertSame('success', PessoasQueChegaram::resumoDoBackup()['cor']);
    }

    public function test_quadro_do_painel_alerta_quando_nunca_rodou(): void
    {
        $this->assertSame('danger', PessoasQueChegaram::resumoDoBackup()['cor']);
    }

    public function test_quadro_do_painel_e_so_do_administrador_e_renderiza(): void
    {
        BackupDeContatos::create(['executado_em' => now(), 'caminho' => $this->pasta, 'mensagens' => 3]);

        $this->actingAs(User::factory()->create(['role' => UserRole::Editor]));
        Livewire::test(PessoasQueChegaram::class)
            ->assertOk()
            ->assertDontSee('Último backup dos contatos');

        $this->actingAs(User::factory()->create(['role' => UserRole::Administrator]));
        Livewire::test(PessoasQueChegaram::class)
            ->assertOk()
            ->assertSee('Último backup dos contatos');
    }

    // ── relatorio mensal ────────────────────────────────────────────

    public function test_relatorio_mensal_cobre_so_o_mes_e_aponta_semanas_sem_execucao(): void
    {
        Mail::fake();

        // Marco fora do mes de referencia: nao pode entrar.
        BackupDeContatos::create(['executado_em' => '2026-04-28 03:00:00', 'caminho' => $this->pasta, 'mensagens' => 9]);
        // Dentro do mes: semanas 1 e 3 (01-07 e 15-21).
        BackupDeContatos::create(['executado_em' => '2026-05-04 03:00:00', 'caminho' => $this->pasta, 'mensagens' => 2]);
        BackupDeContatos::create(['executado_em' => '2026-05-18 03:00:00', 'caminho' => $this->pasta, 'leads' => 1]);

        $this->artisan('backup:relatorio', ['--mes' => '2026-05'])->assertSuccessful();

        Mail::assertSent(RelatorioMensalDeBackups::class, function (RelatorioMensalDeBackups $mail): bool {
            $this->assertSame(2, $mail->execucoes->count());
            $this->assertSame(0, $mail->execucoes->where('mensagens', 9)->count());
            // Blocos de 7 dias a partir do dia 1: 08-14, 22-28 e 29-31.
            $this->assertSame(['08/05 a 14/05', '22/05 a 28/05', '29/05 a 31/05'], $mail->semanasSemBackup);

            return $mail->hasTo('contato@dramarinamariz.com.br');
        });
    }

    public function test_relatorio_mensal_sai_mesmo_sem_nenhuma_execucao(): void
    {
        Mail::fake();

        $this->artisan('backup:relatorio', ['--mes' => '2026-05'])->assertSuccessful();

        Mail::assertSent(RelatorioMensalDeBackups::class, function (RelatorioMensalDeBackups $mail): bool {
            $corpo = $mail->render();

            return $mail->execucoes->isEmpty()
                && str_contains($corpo, 'Nenhum backup foi executado no mês');
        });
    }
}
