<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\EducationType;
use App\Enums\EventFormat;
use App\Enums\StatusMensagem;
use App\Enums\UserRole;
use App\Filament\Resources\EducationItems\EducationItemResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Widgets\AtalhosDoPainel;
use App\Filament\Widgets\ConteudoParaPublicar;
use App\Filament\Widgets\PessoasQueChegaram;
use App\Models\EducationItem;
use App\Models\Lead;
use App\Models\Mensagem;
use App\Models\Post;
use App\Models\User;
use Filament\Pages\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tela inicial do painel (doc 00): contadores, proximo evento, atalhos
 * e o que cada papel enxerga.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(UserRole $papel): User
    {
        $user = User::factory()->create(['role' => $papel]);
        $user->saveAppAuthenticationSecret('BASE32SECRETOTESTE');

        return $user;
    }

    /** Evento publicavel: publicar cobra data, formato e local. */
    private function evento(string $titulo, string $inicio, ContentStatus $status = ContentStatus::Publicado): EducationItem
    {
        return EducationItem::create([
            'type' => EducationType::Evento,
            'title' => $titulo,
            'description' => 'Encontro com gestantes.',
            'status' => $status,
            'starts_at' => $inicio,
            'ends_at' => (new \DateTime($inicio))->modify('+2 hours'),
            'event_format' => EventFormat::Online,
        ]);
    }

    // ── mensagens nao lidas ─────────────────────────────────────────

    public function test_conta_so_as_mensagens_novas(): void
    {
        Mensagem::factory()->count(2)->create(['status' => StatusMensagem::Nova]);
        Mensagem::factory()->create(['status' => StatusMensagem::Lida]);
        Mensagem::factory()->create(['status' => StatusMensagem::Respondida]);
        Mensagem::factory()->create(['status' => StatusMensagem::Arquivada]);

        $this->assertSame(2, PessoasQueChegaram::mensagensNaoLidas());
    }

    public function test_caixa_de_entrada_vazia_conta_zero(): void
    {
        $this->assertSame(0, PessoasQueChegaram::mensagensNaoLidas());
    }

    public function test_link_das_mensagens_abre_a_lista_filtrada_por_nao_lidas(): void
    {
        $url = urldecode(PessoasQueChegaram::urlDasMensagens());

        $this->assertStringContainsString('tableFilters[status][value]=nova', $url);
    }

    // ── leads dos ultimos 7 dias ────────────────────────────────────

    public function test_leads_novos_ignoram_quem_chegou_ha_oito_dias(): void
    {
        Lead::factory()->create(['created_at' => now()->subDays(1)]);
        Lead::factory()->create(['created_at' => now()->subDays(6)]);
        Lead::factory()->create(['created_at' => now()->subDays(8)]);

        $this->assertSame(2, PessoasQueChegaram::leadsNovos());
    }

    public function test_sem_lead_nenhum_o_contador_e_zero(): void
    {
        $this->assertSame(0, PessoasQueChegaram::leadsNovos());
    }

    public function test_link_dos_leads_ja_abre_a_lista_no_periodo_de_sete_dias(): void
    {
        $url = urldecode(PessoasQueChegaram::urlDosLeads());

        $this->assertStringContainsString(
            'tableFilters[periodo][de]='.now()->subDays(7)->toDateString(),
            $url,
        );
    }

    // ── rascunhos pendentes ─────────────────────────────────────────

    public function test_rascunhos_somam_blog_e_educacao_e_ignoram_publicado_e_arquivado(): void
    {
        Post::factory()->count(2)->create();
        Post::factory()->publicado()->create();
        Post::factory()->arquivado()->create();

        EducationItem::factory()->count(3)->create();
        EducationItem::factory()->create(['status' => ContentStatus::Arquivado]);
        // Livro publicado cobra link de compra (doc 01).
        EducationItem::factory()->create(['status' => ContentStatus::Publicado, 'external_url' => 'https://exemplo.com/livro']);

        $this->assertSame(
            ['blog' => 2, 'educacao' => 3, 'total' => 5],
            ConteudoParaPublicar::rascunhos(),
        );
    }

    public function test_sem_rascunho_os_contadores_ficam_em_zero(): void
    {
        Post::factory()->publicado()->create();

        $this->assertSame(['blog' => 0, 'educacao' => 0, 'total' => 0], ConteudoParaPublicar::rascunhos());
    }

    public function test_cada_numero_de_rascunho_abre_a_sua_lista_filtrada(): void
    {
        $this->assertStringContainsString(
            'tableFilters[status][values][0]=rascunho',
            urldecode(ConteudoParaPublicar::urlDosRascunhosDoBlog()),
        );

        $this->assertStringContainsString(
            'tableFilters[status][value]=rascunho',
            urldecode(ConteudoParaPublicar::urlDosRascunhosDeEducacao()),
        );
    }

    // ── proximo evento ──────────────────────────────────────────────

    public function test_proximo_evento_e_o_mais_proximo_no_futuro(): void
    {
        $this->evento('Roda de conversa', now()->addMonth()->toDateTimeString());
        $proximo = $this->evento('Aula aberta', now()->addDays(3)->toDateTimeString());

        $this->assertTrue($proximo->is(ConteudoParaPublicar::proximoEvento()));
    }

    public function test_proximo_evento_ignora_evento_passado_e_rascunho(): void
    {
        $this->evento('Encontro do mês passado', now()->subMonth()->toDateTimeString());
        $this->evento('Oficina ainda em rascunho', now()->addDays(2)->toDateTimeString(), ContentStatus::Rascunho);
        $publicado = $this->evento('Aula aberta', now()->addDays(10)->toDateTimeString());

        $this->assertTrue($publicado->is(ConteudoParaPublicar::proximoEvento()));
    }

    public function test_sem_evento_futuro_o_quadro_diz_que_nao_ha(): void
    {
        $this->evento('Encontro do mês passado', now()->subMonth()->toDateTimeString());

        $this->assertNull(ConteudoParaPublicar::proximoEvento());

        $this->actingAs($this->usuario(UserRole::Administrator));

        Livewire::test(ConteudoParaPublicar::class)
            ->assertOk()
            ->assertSee('Nenhum agendado')
            ->assertSee('Nenhum evento publicado com data futura.');
    }

    public function test_quadro_mostra_a_data_e_o_titulo_do_proximo_evento(): void
    {
        $evento = $this->evento('Aula aberta sobre sono', now()->addDays(5)->setTime(19, 30)->toDateTimeString());

        $this->actingAs($this->usuario(UserRole::Administrator));

        Livewire::test(ConteudoParaPublicar::class)
            ->assertOk()
            ->assertSee($evento->starts_at->format('d/m/Y H:i'))
            ->assertSee('Aula aberta sobre sono');
    }

    // ── atalhos ─────────────────────────────────────────────────────

    public function test_atalhos_apontam_para_as_telas_de_criacao(): void
    {
        $this->actingAs($this->usuario(UserRole::Administrator));

        $atalhos = collect(AtalhosDoPainel::atalhos())->pluck('url', 'rotulo');

        $this->assertSame(PostResource::getUrl('create'), $atalhos['Novo post']);
        $this->assertSame(
            EducationItemResource::getUrl('create', ['tipo' => 'evento']),
            $atalhos['Novo evento'],
        );
        $this->assertSame(
            EducationItemResource::getUrl('create', ['tipo' => 'material']),
            $atalhos['Novo material'],
        );
    }

    public function test_atalhos_aparecem_para_quem_pode_criar_conteudo(): void
    {
        foreach ([UserRole::Administrator, UserRole::Editor] as $papel) {
            $this->actingAs($this->usuario($papel));

            $this->assertTrue(AtalhosDoPainel::canView());

            Livewire::test(AtalhosDoPainel::class)
                ->assertOk()
                ->assertSee('Novo post')
                ->assertSee('Novo evento')
                ->assertSee('Novo material');
        }
    }

    /** O mapa da casa: os seis tipos de Educacao, o blog, leads e usuarios. */
    public function test_o_mapa_cobre_todos_os_modulos_para_o_administrador(): void
    {
        $this->actingAs($this->usuario(UserRole::Administrator));

        $rotulos = collect(AtalhosDoPainel::atalhos())->pluck('rotulo')->all();

        foreach (['Novo post', 'Novo livro', 'Novo e-book', 'Novo curso', 'Novo evento',
            'Novo material', 'Nova formação', 'Ver leads', 'Novo usuário'] as $rotulo) {
            $this->assertContains($rotulo, $rotulos);
        }
    }

    /**
     * Cada atalho passa pela Policy do modulo, nao por um `if` de aparencia:
     * o Editor nao cria usuario, entao o atalho nem e montado.
     */
    public function test_atalhos_respeitam_o_papel_de_quem_entra(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $rotulos = collect(AtalhosDoPainel::atalhos())->pluck('rotulo')->all();

        // Editor cuida do conteudo e le leads (doc 00).
        $this->assertContains('Novo livro', $rotulos);
        $this->assertContains('Nova formação', $rotulos);
        $this->assertContains('Ver leads', $rotulos);

        // Usuarios sao area exclusiva do Administrador.
        $this->assertNotContains('Novo usuário', $rotulos);
        $this->assertFalse($this->usuario(UserRole::Editor)->can('create', User::class));

        Livewire::test(AtalhosDoPainel::class)
            ->assertOk()
            ->assertSee('Novo livro')
            ->assertDontSee('Novo usuário');
    }

    public function test_visitante_sem_permissao_de_criar_nao_ve_atalho_nenhum(): void
    {
        // Ninguem autenticado: mesma resposta que uma Policy fechada daria.
        $this->assertSame([], AtalhosDoPainel::atalhos());
        $this->assertFalse(AtalhosDoPainel::canView());
    }

    // ── papeis ──────────────────────────────────────────────────────

    public function test_editor_ve_os_quadros_que_a_policy_dele_permite(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        // Doc 00: Editor le Mensagens e Leads e cuida do conteudo.
        $this->assertTrue(PessoasQueChegaram::canView());
        $this->assertTrue(ConteudoParaPublicar::canView());
        $this->assertTrue(AtalhosDoPainel::canView());

        // Backup dos contatos é assunto de Administrador.
        Livewire::test(PessoasQueChegaram::class)->assertDontSee('Último backup dos contatos');
    }

    public function test_administrador_ve_todos_os_quadros(): void
    {
        $this->actingAs($this->usuario(UserRole::Administrator));

        $this->assertTrue(PessoasQueChegaram::canView());
        $this->assertTrue(ConteudoParaPublicar::canView());
        $this->assertTrue(AtalhosDoPainel::canView());
        Livewire::test(PessoasQueChegaram::class)->assertSee('Último backup dos contatos');
    }

    public function test_quadro_de_pessoas_some_para_quem_nao_pode_ler_as_listas(): void
    {
        // Sem usuario autenticado nenhuma Policy autoriza: o numero nao vaza.
        $this->assertFalse(PessoasQueChegaram::canView());
        $this->assertFalse(ConteudoParaPublicar::canView());
    }

    public function test_quadros_renderizam_os_numeros_do_dia(): void
    {
        Mensagem::factory()->count(2)->create(['status' => StatusMensagem::Nova]);
        Lead::factory()->create(['created_at' => now()->subDay()]);
        Post::factory()->create();

        $this->actingAs($this->usuario(UserRole::Administrator));

        Livewire::test(PessoasQueChegaram::class)
            ->assertOk()
            ->assertSee('Mensagens não lidas')
            ->assertSee('Leads novos (7 dias)');

        Livewire::test(ConteudoParaPublicar::class)
            ->assertOk()
            ->assertSee('Rascunhos no blog')
            ->assertSee('Rascunhos em Educação')
            ->assertSee('Próximo evento');
    }

    // ── a tela ──────────────────────────────────────────────────────

    public function test_dashboard_responde_para_os_dois_papeis(): void
    {
        foreach ([UserRole::Administrator, UserRole::Editor] as $papel) {
            $this->actingAs($this->usuario($papel));

            $this->get(Dashboard::getUrl())->assertOk();
        }
    }
}
