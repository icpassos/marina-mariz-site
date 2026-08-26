<?php

namespace Tests\Feature;

use App\Enums\EducationType;
use App\Enums\EventFormat;
use App\Enums\Icone;
use App\Enums\UserRole;
use App\Filament\Pages\Configuracoes;
use App\Filament\Pages\ContatosEDados;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\EducationItems\EducationItemResource;
use App\Filament\Resources\EducationItems\Pages\CreateEducationItem;
use App\Filament\Resources\EducationItems\Pages\EditEducationItem;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Links\LinkResource;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Mensagens\MensagemResource;
use App\Filament\Resources\Newsletter\InscricaoNewsletterResource;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\EducationItem;
use App\Models\Post;
use App\Models\User;
use Filament\Support\Enums\Width;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * O tema e so aparencia, mas aparencia que precisa chegar ao navegador:
 * o build tem que existir, a pagina tem que apontar para ele, e as cores
 * que o tema define tem que ser as do DS. Nada aqui testa comportamento.
 */
class TemaPainelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['role' => UserRole::Administrator]);
        $user->saveAppAuthenticationSecret('BASE32SECRETOTESTE');

        return $user;
    }

    /** Caminho do CSS compilado do tema, lido do manifesto do Vite. */
    private function arquivoDoTema(): string
    {
        $manifesto = json_decode(File::get(public_path('build/manifest.json')), true);

        $this->assertArrayHasKey(
            'resources/css/filament/admin/theme.css',
            $manifesto,
            'O tema do painel nao esta no manifesto do Vite — rode npm run build.',
        );

        return public_path('build/'.$manifesto['resources/css/filament/admin/theme.css']['file']);
    }

    public function test_o_build_do_tema_existe(): void
    {
        $this->assertFileExists($this->arquivoDoTema());
    }

    public function test_o_tema_traz_a_mozaic_geo_servida_do_projeto(): void
    {
        $css = File::get($this->arquivoDoTema());

        $this->assertStringContainsString('Mozaic GEO', $css);
        $this->assertMatchesRegularExpression('/mozaic-\d00-[\w-]+\.woff2/', $css);
        // Sem CDN: nenhuma fonte pode vir de fora.
        $this->assertStringNotContainsString('fonts.googleapis.com', $css);
        $this->assertStringNotContainsString('fonts.bunny.net', $css);
    }

    public function test_o_tema_carrega_os_tokens_dos_dois_esquemas_do_ds(): void
    {
        $css = File::get($this->arquivoDoTema());

        // Sereno (.scheme-05) no claro, Oceano (.scheme-04) no escuro.
        $this->assertStringContainsString('--surface-rgb:243 241 232', $css);
        $this->assertStringContainsString('--surface-rgb:27 43 63', $css);
        $this->assertStringContainsString('--accent-text:#5648a8', $css);
        $this->assertStringContainsString('--accent-text:#b8a3f5', $css);
    }

    public function test_a_escala_tipografica_aponta_para_token_e_nao_para_valor(): void
    {
        $css = File::get($this->arquivoDoTema());

        foreach (['--text-xs:var(--caption)', '--text-sm:var(--body-sm)', '--text-2xl:var(--h3)'] as $regra) {
            $this->assertStringContainsString($regra, $css);
        }
    }

    public function test_o_painel_usa_a_fonte_e_as_cores_do_ds(): void
    {
        $painel = filament()->getPanel('admin');

        $this->assertSame('Mozaic GEO', $painel->getFontFamily());

        $cores = $painel->getColors();
        // Violeta 2: 6.43:1 sobre Sereno, o unico acento do DS que passa em AA
        // como texto em fundo claro. E Violeta, o acento de texto do Oceano.
        $this->assertSame('#5648A8', $cores['primary'][600]);
        $this->assertSame('#B8A3F5', $cores['primary'][400]);
        // Serafim Neutro e Preto: superficie e texto do Sereno.
        $this->assertSame('#F3F1E8', $cores['gray'][50]);
        $this->assertSame('#0b1423', $cores['gray'][950]);
    }

    public function test_o_favicon_e_o_simbolo_do_ds(): void
    {
        $this->assertFileExists(public_path('images/marina-simbolo.svg'));
        $this->assertStringContainsString(
            'images/marina-simbolo.svg',
            filament()->getPanel('admin')->getFavicon(),
        );
    }

    public function test_o_login_traz_o_tema_e_o_logotipo(): void
    {
        $resposta = $this->get('/paineladm/login')->assertOk();

        $resposta->assertSee(basename($this->arquivoDoTema()), false);
        // LOGO-P em linha, colorido pelos tokens do esquema.
        $resposta->assertSee('aria-label="Dra. Marina Mariz"', false);
        $resposta->assertSee('var(--logo-mark)', false);
    }

    #[DataProvider('paginasDoPainel')]
    public function test_paginas_do_painel_respondem_com_o_tema(string $rota): void
    {
        $resposta = $this->actingAs($this->admin())->get($rota)->assertOk();

        $resposta->assertSee(basename($this->arquivoDoTema()), false);
        $resposta->assertSee('--font-family: \'Mozaic GEO\'', false);
    }

    public static function paginasDoPainel(): array
    {
        return [
            'painel' => ['/paineladm'],
            'lista' => ['/paineladm/posts'],
            'formulario' => ['/paineladm/posts/create'],
        ];
    }

    // ── wireframe do WordPress ──────────────────────────────────────

    /**
     * Menu no topo, como o do site, e conteudo em largura total. A tela de
     * edicao continua em duas colunas, o wireframe do WordPress — o que
     * mudou foi so onde o menu mora.
     */
    public function test_o_painel_usa_menu_no_topo_e_a_grade_de_duas_colunas(): void
    {
        $painel = filament()->getPanel('admin');

        $this->assertTrue($painel->hasTopNavigation());
        $this->assertSame(Width::Full, $painel->getMaxContentWidth());

        $tela = $this->actingAs($this->admin())->get('/paineladm/posts/create');

        // A faixa de 280px vem do tema, presa a esta classe.
        $tela->assertSee('fi-wp-edicao', false);

        // Um painel por grupo de navegacao, e nao uma lista lateral.
        $tela->assertSee('fi-topbar-nav-groups', false);

        foreach (['Conteúdo', 'Pessoas', 'Sistema'] as $grupo) {
            $tela->assertSee($grupo);
        }
    }

    /**
     * O formulario do post e uma coisa so, dividida em duas colunas por CSS.
     * Este teste existe para que "arrumar o layout" nao perca campo pelo
     * caminho: os da coluna principal e os da coluna de apoio tem que estar
     * todos no mesmo formulario.
     */
    public function test_a_edicao_de_post_traz_as_duas_colunas_no_mesmo_formulario(): void
    {
        $post = Post::factory()->create();

        $formulario = Livewire::actingAs($this->admin())
            ->test(EditPost::class, ['record' => $post->getKey()]);

        // Coluna principal: o que se escreve.
        foreach (['title', 'slug', 'description', 'content', 'closing_cta', 'relacionados'] as $campo) {
            $formulario->assertFormFieldVisible($campo);
        }

        // Coluna de apoio: publicacao, categoria, imagem e SEO.
        foreach (['status', 'published_at', 'author', 'category_id', 'image_id', 'seo_title', 'seo_description', 'seo_image_id'] as $campo) {
            $formulario->assertFormFieldVisible($campo);
        }
    }

    /**
     * Educacao tambem e uma coisa so em duas colunas — e o Tipo, que decide o
     * formulario inteiro, mora agora na coluna da direita. O teste existe para
     * que mover campo de coluna nunca perca campo pelo caminho.
     */
    public function test_a_edicao_de_educacao_traz_as_duas_colunas_no_mesmo_formulario(): void
    {
        $evento = EducationItem::create([
            'type' => EducationType::Evento,
            'title' => 'Roda de conversa',
            'description' => 'Encontro com gestantes.',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'event_format' => EventFormat::Online,
        ]);

        $formulario = Livewire::actingAs($this->admin())
            ->test(EditEducationItem::class, ['record' => $evento->getKey()]);

        // Coluna principal: o item.
        foreach (['title', 'description', 'image_id', 'starts_at', 'ends_at', 'event_format'] as $campo) {
            $formulario->assertFormFieldVisible($campo);
        }

        // Coluna de apoio: tipo, publicacao e SEO.
        foreach (['type', 'status', 'seo_title', 'seo_description', 'seo_image_id'] as $campo) {
            $formulario->assertFormFieldVisible($campo);
        }

        // Criar tambem: o tipo continua no schema e continua reativo.
        Livewire::actingAs($this->admin())
            ->withQueryParams(['tipo' => EducationType::Livro->value])
            ->test(CreateEducationItem::class)
            ->assertFormSet(['type' => EducationType::Livro])
            ->assertFormFieldVisible('type')
            ->assertFormFieldVisible('publisher');
    }

    /**
     * Os botoes de acao aparecem duas vezes — topo da coluna da direita e fim
     * da pagina — e sao os mesmos: um `submit` cada, um atalho de teclado so,
     * entao um clique nunca vira dois envios.
     */
    #[DataProvider('telasComBotaoDeSalvar')]
    public function test_os_botoes_de_salvar_aparecem_no_topo_e_no_fim(\Closure $tela, string $rotulo): void
    {
        $html = $tela($this->admin())->html();

        // Dois `submit`, um por bloco: o botao existe nos dois lugares e cada
        // clique manda um envio so, porque os dois pertencem ao mesmo <form>.
        $this->assertSame(2, substr_count($html, 'type="submit"'));
        $this->assertStringContainsString($rotulo, $html);
        $this->assertStringContainsString('acoes-do-topo', $html);
        $this->assertStringContainsString('form-actions', $html);
        // Um `mod+s` so: dois atalhos iguais mandariam dois envios.
        $this->assertSame(1, substr_count($html, 'mod-s="'));
    }

    /**
     * Os icones do painel sao os do site (Phosphor, DS_Marina.html), inclusive
     * os que o Filament traz de fabrica: o `viewBox` de 256 e a assinatura do
     * Phosphor, o de 24 e do Heroicon que ficou para tras.
     */
    public function test_o_painel_usa_os_icones_do_site(): void
    {
        foreach (Icone::cases() as $icone) {
            $this->assertFileExists(
                resource_path('svg/phosphor/'.str_replace('ph-', '', $icone->value).'.svg'),
                $icone->value,
            );
        }

        $html = $this->actingAs($this->admin())->get(PostResource::getUrl('index'))->getContent();

        $this->assertStringContainsString('viewBox="0 0 256 256"', (string) $html);

        // O que sobra em 24x24 e o indicador de carregamento do Filament, que
        // nao e icone do DS — e uma animacao. Nenhum Heroicon fora disso.
        preg_match_all('/<svg[^>]*viewBox="0 0 24 24"[^>]*>/', (string) $html, $restos);

        foreach ($restos[0] as $tag) {
            $this->assertStringContainsString('fi-loading-indicator', $tag);
        }
    }

    public function test_campo_de_link_ja_chega_com_https_ao_receber_o_cursor(): void
    {
        $html = Livewire::actingAs($this->admin())
            ->withQueryParams(['tipo' => EducationType::Livro->value])
            ->test(CreateEducationItem::class)
            ->html();

        // Digitar "https://" em todo link era trabalho que a tela pode fazer.
        $this->assertStringContainsString("this.value = 'https://'", $html);
    }

    /** @return array<string, array{\Closure, string}> */
    public static function telasComBotaoDeSalvar(): array
    {
        return [
            'editar post' => [
                fn (User $admin) => Livewire::actingAs($admin)
                    ->test(EditPost::class, ['record' => Post::factory()->create()->getKey()]),
                // Rascunho: o botao de enviar publica, e diz isso.
                'Publicar',
            ],
            'criar post' => [
                fn (User $admin) => Livewire::actingAs($admin)->test(CreatePost::class),
                'Publicar',
            ],
            'criar item de educação' => [
                fn (User $admin) => Livewire::actingAs($admin)
                    ->withQueryParams(['tipo' => EducationType::Livro->value])
                    ->test(CreateEducationItem::class),
                'Publicar',
            ],
            'editar item de educação' => [
                fn (User $admin) => Livewire::actingAs($admin)->test(EditEducationItem::class, [
                    'record' => EducationItem::create([
                        'type' => EducationType::Livro,
                        'title' => 'Gestação sem medo',
                    ])->getKey(),
                ]),
                'Publicar',
            ],
        ];
    }

    /**
     * Cada secao diz o que e e, principalmente, de onde vem ou para onde vai.
     * O trecho conferido e o endereco da pagina do site que a secao alimenta:
     * so aparece se o texto certo estiver la.
     */
    #[DataProvider('secoesQueSeExplicam')]
    public function test_cada_secao_diz_de_onde_vem_e_para_onde_vai(\Closure $url, string $trecho): void
    {
        $this->actingAs($this->admin())
            ->get($url())
            ->assertOk()
            ->assertSee($trecho, false);
    }

    /** O endereco so existe com a aplicacao de pe, entao vem em Closure. */
    /** @return array<string, array{\Closure, string}> */
    public static function secoesQueSeExplicam(): array
    {
        return [
            'educação' => [fn (): string => EducationItemResource::getUrl('index'), '/educacao'],
            'e-books' => [fn (): string => EducationItemResource::getUrl('index', ['tipo' => 'ebook']), '/educacao/materiais-gratuitos'],
            'blog' => [fn (): string => PostResource::getUrl('index'), '/blog/nome-do-post'],
            'categorias' => [fn (): string => CategoryResource::getUrl('index'), 'não vira aba'],
            'links' => [fn (): string => LinkResource::getUrl('index'), '/links'],
            'contatos e dados' => [fn (): string => ContatosEDados::getUrl(), '/politica-de-privacidade'],
            'mensagens' => [fn (): string => MensagemResource::getUrl('index'), '/educacao/formacao-profissional'],
            'leads' => [fn (): string => LeadResource::getUrl('index'), '/educacao/materiais-gratuitos'],
            'newsletter' => [fn (): string => InscricaoNewsletterResource::getUrl('index'), '/descadastrar'],
            'mídia' => [fn (): string => MediaResource::getUrl('index'), 'Blog e de Educação'],
            'configurações' => [fn (): string => Configuracoes::getUrl(), 'todas as páginas do site'],
            'usuários' => [fn (): string => UserResource::getUrl('index'), '/paineladm'],
        ];
    }
}
