<?php

namespace Tests\Feature;

use App\Enums\StatusMensagem;
use App\Enums\UserRole;
use App\Filament\Exports\InscricaoNewsletterExporter;
use App\Filament\Exports\LeadExporter;
use App\Filament\Exports\MensagemExporter;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Mensagens\MensagemResource;
use App\Filament\Resources\Mensagens\Pages\ListMensagens;
use App\Filament\Resources\Newsletter\InscricaoNewsletterResource;
use App\Filament\Resources\Newsletter\Pages\ListInscricoes;
use App\Models\InscricaoNewsletter;
use App\Models\Lead;
use App\Models\LeadDownload;
use App\Models\Mensagem;
use App\Models\OutboxJob;
use App\Models\User;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Painel do modulo Pessoas: papeis, filtros e exportacao (docs 04 e 08).
 */
class PessoasPainelTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(UserRole $papel): User
    {
        $user = User::factory()->create(['role' => $papel]);
        $user->saveAppAuthenticationSecret('BASE32SECRETOTESTE');

        return $user;
    }

    private function exportador(string $classe, $registro): array
    {
        $exportador = new $classe(new Export, array_combine(
            $nomes = array_map(fn ($c) => $c->getName(), $classe::getColumns()),
            $nomes,
        ), []);

        return $exportador($registro);
    }

    // ── papeis ──────────────────────────────────────────────────────

    public function test_editor_ve_as_tres_listas(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->get(MensagemResource::getUrl('index'))->assertOk();
        $this->get(LeadResource::getUrl('index'))->assertOk();
        $this->get(InscricaoNewsletterResource::getUrl('index'))->assertOk();
    }

    public function test_editor_exporta_mas_nao_altera_nem_anota_nem_exclui(): void
    {
        $editor = $this->usuario(UserRole::Editor);
        $mensagem = Mensagem::factory()->create();

        $this->actingAs($editor);

        $this->assertTrue($editor->can('viewAny', Mensagem::class));
        $this->assertTrue($editor->can('export', Mensagem::class));

        // Alterar status, anotar e excluir passam todos pela mesma Policy.
        $this->assertFalse($editor->can('update', $mensagem));
        $this->assertFalse($editor->can('delete', $mensagem));
        $this->assertFalse($editor->can('create', Mensagem::class));

        // E as acoes da tabela somem para ele.
        Livewire::test(ListMensagens::class)
            ->assertTableActionHidden('anotar', $mensagem)
            ->assertTableActionHidden('arquivar', $mensagem)
            ->assertTableActionHidden('marcarLida', $mensagem)
            ->assertTableActionHidden('delete', $mensagem);
    }

    public function test_administrador_anota_arquiva_e_exclui(): void
    {
        $admin = $this->usuario(UserRole::Administrator);
        $mensagem = Mensagem::factory()->create();

        $this->actingAs($admin);

        Livewire::test(ListMensagens::class)
            ->callTableAction('anotar', $mensagem, ['anotacao' => 'Ligar na terça.'])
            ->assertHasNoTableActionErrors();

        $this->assertSame('Ligar na terça.', $mensagem->refresh()->anotacao);

        Livewire::test(ListMensagens::class)->callTableAction('arquivar', $mensagem);
        $this->assertSame(StatusMensagem::Arquivada, $mensagem->refresh()->status);

        Livewire::test(ListMensagens::class)->callTableAction('delete', $mensagem);
        $this->assertSame(0, Mensagem::count());
    }

    public function test_lead_vira_cliente_so_pelo_administrador(): void
    {
        $lead = Lead::factory()->has(LeadDownload::factory(), 'downloads')->create();

        $this->actingAs($this->usuario(UserRole::Editor));
        Livewire::test(ListLeads::class)->assertTableActionHidden('marcarCliente', $lead);

        $this->actingAs($this->usuario(UserRole::Administrator));
        Livewire::test(ListLeads::class)->callTableAction('marcarCliente', $lead);

        $this->assertTrue($lead->refresh()->eh_cliente);
    }

    public function test_so_o_administrador_ve_pendencias_da_outbox(): void
    {
        $mensagem = Mensagem::factory()->create();
        OutboxJob::factory()->create([
            'assunto_id' => $mensagem->getKey(),
            'submission_id' => $mensagem->submission_id,
            'tentativas' => 2,
            'ultimo_erro' => 'RuntimeException #500',
        ]);

        $this->actingAs($this->usuario(UserRole::Administrator))
            ->get(MensagemResource::getUrl('view', ['record' => $mensagem]))
            ->assertSee('RuntimeException #500');

        $this->actingAs($this->usuario(UserRole::Editor))
            ->get(MensagemResource::getUrl('view', ['record' => $mensagem]))
            ->assertDontSee('RuntimeException #500');
    }

    // ── exportacao ──────────────────────────────────────────────────

    public function test_exportacao_e_so_csv_em_disco_privado(): void
    {
        $exportador = new MensagemExporter(new Export, [], []);

        $this->assertSame([ExportFormat::Csv], $exportador->getFormats());
        $this->assertSame('local', $exportador->getFileDisk());
    }

    public function test_valor_comecado_por_igual_sai_neutralizado_no_csv(): void
    {
        $mensagem = Mensagem::factory()->create([
            'nome' => '=HYPERLINK("http://mal.example","clique")',
            'texto' => '+1+1',
            'whatsapp' => '-31999990000',
            'email' => 'ana@example.com',
        ]);

        $linha = $this->exportador(MensagemExporter::class, $mensagem);

        $this->assertContains("'=HYPERLINK(\"http://mal.example\",\"clique\")", $linha);
        $this->assertContains("'+1+1", $linha);
        $this->assertContains("'-31999990000", $linha);

        // Valor comum nao ganha apostrofo.
        $this->assertContains('ana@example.com', $linha);
    }

    public function test_arroba_tambem_e_neutralizado(): void
    {
        $inscricao = InscricaoNewsletter::factory()->create(['nome' => '@SUM(1)']);

        $this->assertContains("'@SUM(1)", $this->exportador(InscricaoNewsletterExporter::class, $inscricao));
    }

    public function test_csv_nao_leva_ip_pseudonimizado_nem_anotacao_interna(): void
    {
        $colunas = fn (string $exportador): array => array_map(
            fn ($c) => $c->getName(),
            $exportador::getColumns(),
        );

        foreach ([MensagemExporter::class, LeadExporter::class, InscricaoNewsletterExporter::class] as $exportador) {
            $nomes = $colunas($exportador);

            $this->assertNotContains('ip_hash', $nomes);
            $this->assertNotContains('anotacao', $nomes);
            $this->assertNotContains('submission_id', $nomes);
            $this->assertNotContains('id', $nomes);
        }
    }

    public function test_csv_da_newsletter_leva_o_link_de_descadastro(): void
    {
        $inscricao = InscricaoNewsletter::factory()->create();

        $this->assertContains($inscricao->linkDeDescadastro(), $this->exportador(InscricaoNewsletterExporter::class, $inscricao));
    }

    public function test_exportacao_usa_o_mesmo_recorte_da_lista(): void
    {
        // Lead sem download nao aparece na lista; tambem nao pode aparecer
        // na exportacao, que nao consulta a Policy registro por registro.
        Lead::factory()->count(2)->has(LeadDownload::factory(), 'downloads')->create();
        $orfao = Lead::factory()->create();

        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::test(ListLeads::class)->assertCanNotSeeTableRecords([$orfao]);

        $this->assertSame(3, Lead::count());
        $this->assertSame(2, LeadResource::escopoVisivel(Lead::query())->count());
    }

    public function test_exportacao_filtrada_nao_amplia_o_conjunto_visivel(): void
    {
        $arquivada = Mensagem::factory()->create(['status' => StatusMensagem::Arquivada]);
        $nova = Mensagem::factory()->create(['status' => StatusMensagem::Nova]);

        $this->actingAs($this->usuario(UserRole::Editor));

        $componente = Livewire::test(ListMensagens::class)
            ->filterTable('status', StatusMensagem::Arquivada->value)
            ->assertCanSeeTableRecords([$arquivada])
            ->assertCanNotSeeTableRecords([$nova]);

        // A exportacao parte da mesma query filtrada da tabela.
        $consulta = $componente->instance()->getFilteredTableQuery();

        $this->assertSame(
            [$arquivada->getKey()],
            MensagemResource::escopoVisivel($consulta)->pluck('mensagens.id')->all(),
        );
    }

    public function test_botao_de_exportar_aparece_para_os_dois_papeis(): void
    {
        foreach ([UserRole::Administrator, UserRole::Editor] as $papel) {
            $this->actingAs($this->usuario($papel));

            Livewire::test(ListMensagens::class)->assertActionVisible('export');
            Livewire::test(ListLeads::class)->assertActionVisible('export');
            Livewire::test(ListInscricoes::class)->assertActionVisible('export');
        }
    }

    public function test_exportacao_de_ponta_a_ponta_grava_csv_neutralizado_no_disco_privado(): void
    {
        Storage::fake('local');

        $mensagem = Mensagem::factory()->create(['nome' => '=1+1', 'email' => 'ana@example.com']);

        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::test(ListMensagens::class)
            ->callAction('export', data: [
                'columnMap' => collect(MensagemExporter::getColumns())
                    ->mapWithKeys(fn ($c) => [$c->getName() => ['isEnabled' => true, 'label' => $c->getLabel()]])
                    ->all(),
            ])
            ->assertHasNoActionErrors();

        $export = Export::sole();
        $disco = Storage::disk('local');
        $csv = collect($disco->files($export->getFileDirectory()))
            ->map(fn (string $f): string => $disco->get($f))
            ->implode("\n");

        $this->assertStringContainsString("'=1+1", $csv);
        $this->assertStringNotContainsString($mensagem->ip_hash, $csv);
    }
}
