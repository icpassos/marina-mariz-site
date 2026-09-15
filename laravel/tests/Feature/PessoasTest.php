<?php

namespace Tests\Feature;

use App\Enums\OrigemMensagem;
use App\Enums\StatusInscricao;
use App\Models\Consentimento;
use App\Models\InscricaoNewsletter;
use App\Models\Lead;
use App\Models\LeadDownload;
use App\Models\Media;
use App\Models\Mensagem;
use App\Models\OutboxJob;
use App\Support\IpPseudonimo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Entradas de formulario, idempotencia e LGPD (docs 04 e 08).
 */
class PessoasTest extends TestCase
{
    use RefreshDatabase;

    private const IP = '203.0.113.9';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['pessoas.ip_hash_key' => 'chave-de-teste', 'pessoas.politica_versao' => '2026-08-21']);
    }

    private function envia(string $uri, array $dados): TestResponse
    {
        return $this->call('POST', $uri, $dados, [], [], [
            'REMOTE_ADDR' => self::IP,
            'HTTP_ACCEPT' => 'application/json',
        ]);
    }

    private function dadosDeContato(string $submissionId): array
    {
        return [
            'submission_id' => $submissionId,
            'iniciado_em' => Crypt::encryptString((string) now()->subSeconds(4)->timestamp),
            'nome' => 'Ana Souza',
            'email' => 'ana@example.com',
            'whatsapp' => '(31) 99999-0000',
            'texto' => 'Gostaria de marcar uma consulta.',
            'ciencia_politica' => '1',
        ];
    }

    private function material(): Media
    {
        Storage::disk('local')->put('midia/guia.pdf', '%PDF-1.4');

        return Media::factory()->create([
            'path' => 'midia/guia.pdf',
            'name' => 'Guia do pré-natal',
            'original_name' => 'guia.pdf',
            'mime_type' => 'application/pdf',
        ]);
    }

    // ── idempotencia ────────────────────────────────────────────────

    public function test_mesma_chave_grava_um_registro_e_um_item_no_outbox(): void
    {
        $chave = (string) Str::uuid();

        $this->envia('/formularios/contato', $this->dadosDeContato($chave))->assertOk();
        $this->envia('/formularios/contato', $this->dadosDeContato($chave))->assertOk();

        $this->assertSame(1, Mensagem::count());
        $this->assertSame(1, OutboxJob::where('submission_id', $chave)->count());
    }

    public function test_mesmo_email_nao_cria_segundo_lead_e_o_historico_cresce(): void
    {
        $material = $this->material();

        foreach ([Str::uuid(), Str::uuid()] as $chave) {
            $this->envia('/formularios/material', [
                'submission_id' => (string) $chave,
                'nome' => 'Ana Souza',
                'email' => 'ana@example.com',
                'media_id' => $material->getKey(),
                'ciencia_politica' => '1',
            ])->assertOk();
        }

        $this->assertSame(1, Lead::count());
        $this->assertSame(2, LeadDownload::count());
    }

    public function test_download_entrega_link_de_tres_dias_na_hora(): void
    {
        $material = $this->material();

        $resposta = $this->envia('/formularios/material', [
            'submission_id' => (string) Str::uuid(),
            'nome' => 'Ana Souza',
            'email' => 'ana@example.com',
            'media_id' => $material->getKey(),
            'ciencia_politica' => '1',
        ]);

        $resposta->assertOk();
        $this->get($resposta->json('download_url'))->assertOk();
    }

    // ── validacao e anti-spam ───────────────────────────────────────

    public function test_sem_ciencia_da_politica_nao_grava(): void
    {
        $dados = $this->dadosDeContato((string) Str::uuid());
        unset($dados['ciencia_politica']);

        $this->envia('/formularios/contato', $dados)->assertStatus(422);
        $this->assertSame(0, Mensagem::count());
    }

    public function test_honeypot_preenchido_nao_grava(): void
    {
        $dados = $this->dadosDeContato((string) Str::uuid()) + ['website' => 'http://spam.example'];

        $this->envia('/formularios/contato', $dados)->assertStatus(422);
        $this->assertSame(0, Mensagem::count());
    }

    public function test_envio_rapido_demais_nao_grava(): void
    {
        $dados = $this->dadosDeContato((string) Str::uuid());
        $dados['iniciado_em'] = Crypt::encryptString((string) now()->timestamp);

        $this->envia('/formularios/contato', $dados)->assertStatus(422);
        $this->assertSame(0, Mensagem::count());
        $this->assertSame(0, OutboxJob::count());
    }

    public function test_contato_nao_aceita_campos_de_formacao_profissional(): void
    {
        $dados = $this->dadosDeContato((string) Str::uuid()) + ['profissao' => 'Enfermeira'];

        $this->envia('/formularios/contato', $dados)->assertStatus(422);
    }

    public function test_formacao_profissional_cai_na_mesma_caixa_com_os_extras(): void
    {
        $this->envia('/formularios/formacao-profissional', [
            'submission_id' => (string) Str::uuid(),
            'iniciado_em' => Crypt::encryptString((string) now()->subSeconds(4)->timestamp),
            'nome' => 'Bia Lima',
            'email' => 'bia@example.com',
            'texto' => 'Quero saber da próxima turma.',
            'profissao' => 'Enfermeira',
            'registro_profissional' => 'COREN 12345',
            'ciencia_politica' => '1',
        ])->assertOk();

        $mensagem = Mensagem::sole();
        $this->assertSame(OrigemMensagem::FormacaoProfissional, $mensagem->origem);
        $this->assertSame('COREN 12345', $mensagem->registro_profissional);
    }

    // ── LGPD ────────────────────────────────────────────────────────

    public function test_consentimento_de_newsletter_grava_versao_data_e_ip_pseudonimizado(): void
    {
        $this->envia('/formularios/newsletter', [
            'submission_id' => (string) Str::uuid(),
            'nome' => 'Ana Souza',
            'email' => 'ana@example.com',
            'origem' => 'blog',
            'ciencia_politica' => '1',
            'aceita_newsletter' => '1',
        ])->assertOk();

        $consentimento = Consentimento::sole();

        $this->assertSame(Consentimento::NEWSLETTER, $consentimento->finalidade);
        $this->assertSame('2026-08-21', $consentimento->versao_aviso);
        $this->assertNotNull($consentimento->concedido_em);
        $this->assertNull($consentimento->revogado_em);
        $this->assertSame(IpPseudonimo::de(self::IP), $consentimento->ip_hash);
    }

    public function test_ip_em_claro_nao_e_gravado_em_lugar_nenhum(): void
    {
        $material = $this->material();

        $this->envia('/formularios/contato', $this->dadosDeContato((string) Str::uuid()))->assertOk();
        $this->envia('/formularios/newsletter', [
            'submission_id' => (string) Str::uuid(),
            'email' => 'bia@example.com',
            'origem' => 'blog',
            'ciencia_politica' => '1',
            'aceita_newsletter' => '1',
        ])->assertOk();
        $this->envia('/formularios/material', [
            'submission_id' => (string) Str::uuid(),
            'nome' => 'Cris Dias',
            'email' => 'cris@example.com',
            'media_id' => $material->getKey(),
            'ciencia_politica' => '1',
            'aceita_newsletter' => '1',
        ])->assertOk();

        $tabelas = ['mensagens', 'leads', 'lead_downloads', 'inscricoes_newsletter', 'consentimentos', 'outbox_jobs'];

        foreach ($tabelas as $tabela) {
            foreach (DB::table($tabela)->get() as $linha) {
                foreach ((array) $linha as $valor) {
                    $this->assertStringNotContainsString(self::IP, (string) $valor, "IP em claro em {$tabela}");
                }
            }
        }

        // E o hash foi gravado: pseudonimizar nao virou "nao gravar nada".
        $this->assertSame(IpPseudonimo::de(self::IP), Mensagem::sole()->ip_hash);
    }

    public function test_newsletter_exige_a_caixa_propria_marcada(): void
    {
        $this->envia('/formularios/newsletter', [
            'submission_id' => (string) Str::uuid(),
            'email' => 'ana@example.com',
            'origem' => 'blog',
            'ciencia_politica' => '1',
        ])->assertStatus(422);

        $this->assertSame(0, InscricaoNewsletter::count());
    }

    public function test_material_entrega_mesmo_sem_consentimento_de_newsletter(): void
    {
        $material = $this->material();

        $this->envia('/formularios/material', [
            'submission_id' => (string) Str::uuid(),
            'nome' => 'Ana Souza',
            'email' => 'ana@example.com',
            'media_id' => $material->getKey(),
            'ciencia_politica' => '1',
        ])->assertOk();

        $this->assertSame(1, Lead::count());
        $this->assertSame(0, Consentimento::count());
    }

    // ── descadastro ─────────────────────────────────────────────────

    public function test_descadastro_funciona_com_um_clique_e_fica_registrado(): void
    {
        $inscricao = InscricaoNewsletter::factory()->create();
        $inscricao->consentimentos()->create([
            'finalidade' => Consentimento::NEWSLETTER,
            'versao_aviso' => '2026-08-21',
            'concedido_em' => now(),
        ]);

        $link = $inscricao->linkDeDescadastro();

        $this->get($link)->assertOk()->assertSee('descadastrada', escape: false);

        $inscricao->refresh();
        $this->assertSame(StatusInscricao::Descadastrado, $inscricao->status);
        $this->assertNotNull($inscricao->descadastrado_em);
        $this->assertNotNull($inscricao->consentimentos()->sole()->revogado_em);

        // Token de uso unico: o mesmo link nao serve de novo.
        $this->assertNull($inscricao->descadastro_token);
        $this->get($link)->assertOk()->assertSee('já foi usado', escape: false);
    }

    public function test_quem_volta_depois_de_sair_reativa_com_token_novo(): void
    {
        $inscricao = InscricaoNewsletter::factory()->create(['email' => 'ana@example.com']);
        $tokenAntigo = $inscricao->descadastro_token;
        $inscricao->descadastrar();

        $this->envia('/formularios/newsletter', [
            'submission_id' => (string) Str::uuid(),
            'email' => 'ana@example.com',
            'origem' => 'blog',
            'ciencia_politica' => '1',
            'aceita_newsletter' => '1',
        ])->assertOk();

        $inscricao->refresh();
        $this->assertSame(StatusInscricao::Ativo, $inscricao->status);
        $this->assertNotNull($inscricao->descadastro_token);
        $this->assertNotSame($tokenAntigo, $inscricao->descadastro_token);
        $this->assertSame(1, InscricaoNewsletter::count());
    }
}
