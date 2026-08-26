<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Links\Pages\CreateLink;
use App\Filament\Resources\Links\Pages\EditLink;
use App\Filament\Resources\Links\Pages\ListLinks;
use App\Models\Link;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\LinksPadraoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pagina /links e a tela que a alimenta (doc 09). O que estes testes
 * defendem: a pagina e avulsa de verdade — sem menu, sem rodape e fora do
 * Google — e so mostra o que esta publicado, na ordem do painel.
 */
class LinksTest extends TestCase
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

    // ── pagina publica ──────────────────────────────────────────────

    public function test_mostra_so_o_que_esta_publicado_na_ordem_do_painel(): void
    {
        Link::factory()->create(['title' => 'Segundo', 'position' => 2]);
        Link::factory()->create(['title' => 'Primeiro', 'position' => 1]);
        Link::factory()->rascunho()->create(['title' => 'Rascunho']);
        Link::factory()->create(['title' => 'Arquivado', 'status' => ContentStatus::Arquivado]);

        $html = $this->get('/links')->assertOk()->getContent();

        $this->assertStringNotContainsString('Rascunho', $html);
        $this->assertStringNotContainsString('Arquivado', $html);
        $this->assertLessThan(
            strpos($html, 'Segundo'),
            strpos($html, 'Primeiro'),
            'a ordem da pagina nao seguiu o campo position',
        );
    }

    public function test_entra_sem_menu_e_sem_rodape(): void
    {
        $html = $this->get('/links')->assertOk()->getContent();

        $this->assertStringNotContainsString('<nav class="nav', $html);
        $this->assertStringNotContainsString('class="footer', $html);
    }

    public function test_fica_fora_do_google_e_fora_do_sitemap(): void
    {
        $this->get('/links')->assertSee('content="noindex,follow"', escape: false);

        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/links', escape: false);
    }

    public function test_link_de_fora_abre_em_aba_nova_e_link_do_site_nao(): void
    {
        Link::factory()->create(['title' => 'Instagram', 'url' => 'https://instagram.com/dramarinamariz']);
        Link::factory()->create(['title' => 'Blog daqui', 'url' => '/blog']);

        $html = $this->get('/links')->assertOk()->getContent();

        $this->assertStringContainsString('href="https://instagram.com/dramarinamariz" target="_blank" rel="noopener"', $html);
        $this->assertStringContainsString('href="/blog">', $html);
    }

    /** Exigencia da Resolucao CFM 2.336/2023; aqui nao ha rodape para carregar. */
    public function test_registro_profissional_aparece_mesmo_sem_o_rodape(): void
    {
        $this->get('/links')
            ->assertOk()
            ->assertSee('CRM-MG 48.386')
            ->assertSee('RQE 30.992');
    }

    public function test_pagina_sem_nenhum_link_publicado_nao_quebra(): void
    {
        $this->get('/links')->assertOk()->assertSee('Em breve');
    }

    /**
     * A identidade e a mesma do site: assinatura da marca e a lanterna do
     * ponteiro que os cards do site ja usam. Sem o sprite nada disso
     * desenha, e a pagina volta a ser um bloco liso.
     */
    public function test_veste_a_identidade_do_site(): void
    {
        Link::factory()->create(['title' => 'Um link']);

        $html = $this->get('/links')->assertOk()->getContent();

        $this->assertStringContainsString('href="#logo-p"', $html, 'a assinatura sumiu');
        $this->assertStringContainsString('<symbol id="logo-p"', $html, 'o sprite da marca nao foi incluido');
        $this->assertStringContainsString('data-glow', $html, 'os cards perderam a lanterna do ponteiro');

        // A marca e estatica: o selo girando saiu a pedido da dona.
        $this->assertStringNotContainsString('href="#badge"', $html);
    }

    /** Duas linhas e hífen simples, como a dona pediu. */
    public function test_linha_do_registro_quebra_antes_do_rqe_e_usa_hifen(): void
    {
        $this->get('/links')
            ->assertOk()
            ->assertSee('Dra. Marina Mariz - CRM-MG 48.386</b> · Ginecologia e Obstetrícia<br>RQE 30.992 · Medicina Fetal - RQE 30.993', escape: false);
    }

    // ── painel ──────────────────────────────────────────────────────

    public function test_editor_cria_link(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::test(CreateLink::class)
            ->fillForm([
                'title' => 'Agendar consulta',
                'url' => '/contato',
                'status' => ContentStatus::Publicado->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('links', ['title' => 'Agendar consulta', 'url' => '/contato']);
    }

    public function test_endereco_precisa_ser_https_ou_do_proprio_site(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::test(CreateLink::class)
            ->fillForm([
                'title' => 'Inseguro',
                'url' => 'http://exemplo.com',
                'status' => ContentStatus::Publicado->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['url']);
    }

    public function test_rascunho_publica_sem_endereco_mas_publicado_nao(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::test(CreateLink::class)
            ->fillForm(['title' => 'Ideia solta', 'status' => ContentStatus::Rascunho->value])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateLink::class)
            ->fillForm(['title' => 'Sem destino', 'status' => ContentStatus::Publicado->value])
            ->call('create')
            ->assertHasFormErrors(['url']);
    }

    public function test_editor_nao_arquiva_link(): void
    {
        $link = Link::factory()->create();

        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::test(EditLink::class, ['record' => $link->getKey()])
            ->assertFormFieldExists(
                'status',
                fn ($campo): bool => $campo->isOptionDisabled(ContentStatus::Arquivado->value, 'Arquivado'),
            );
    }

    public function test_editor_nao_envia_link_a_lixeira(): void
    {
        $link = Link::factory()->create();

        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::test(ListLinks::class)
            ->assertTableActionHidden('delete', $link);
    }

    public function test_administrador_arrasta_para_reordenar(): void
    {
        $primeiro = Link::factory()->create(['position' => 1]);
        $segundo = Link::factory()->create(['position' => 2]);

        $this->actingAs($this->usuario(UserRole::Administrator));

        Livewire::test(ListLinks::class)
            ->call('reorderTable', [$segundo->getKey(), $primeiro->getKey()]);

        $this->assertSame([$segundo->getKey(), $primeiro->getKey()], Link::noAr()->pluck('id')->all());
    }

    /**
     * A miniatura e uma chave estrangeira com `restrictOnDelete`. Se a tela
     * de Midia nao souber disso, ela diz "sem uso" e o banco recusa apagar.
     */
    public function test_biblioteca_de_midia_enxerga_a_miniatura_em_uso(): void
    {
        Storage::disk('local')->put($caminho = 'midia/mini.png', 'x');

        $midia = Media::factory()->create(['path' => $caminho, 'mime_type' => 'image/png']);

        Link::factory()->create(['title' => 'Comunidade', 'image_id' => $midia->getKey()]);

        $usos = $midia->fresh()->usos();

        $this->assertCount(1, $usos);
        $this->assertStringContainsString('Links · Comunidade (miniatura)', $usos[0]);
    }

    public function test_visualizar_so_aparece_para_link_publicado(): void
    {
        $publicado = Link::factory()->create();
        $rascunho = Link::factory()->rascunho()->create();

        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::test(ListLinks::class)
            ->assertTableActionVisible('verNoSite', $publicado)
            ->assertTableActionHidden('verNoSite', $rascunho);
    }

    public function test_a_pagina_nasce_com_os_botoes_padrao_e_o_seeder_nao_duplica(): void
    {
        // /links vazia e pagina em branco no Instagram: o deploy leva estes
        // botoes prontos, e rodar o seeder de novo nao cria copia.
        $this->seed(LinksPadraoSeeder::class);
        $this->seed(LinksPadraoSeeder::class);

        $this->assertSame(7, Link::count());
        $this->assertSame(7, Link::noAr()->count());

        $this->get('/links')
            ->assertOk()
            ->assertSee('Agendar consulta')
            ->assertSee('Materiais gratuitos');
    }
}
