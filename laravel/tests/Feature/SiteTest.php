<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\DadosDoSite;
use App\Models\Post;
use App\Models\TextoLegal;
use App\Services\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Site publico portado do HTML estatico para Blade. O que estes testes
 * defendem: o texto que a dona revisou continua saindo igual, e o que veio
 * do painel realmente muda quando o painel muda.
 */
class SiteTest extends TestCase
{
    use RefreshDatabase;

    /** Endereco => titulo esperado, na ordem do menu. */
    public static function paginas(): array
    {
        return [
            'inicio' => ['/', 'Dra. Marina Mariz — Ginecologia e Obstetrícia', 'https://dramarinamariz.com.br/'],
            'sobre' => ['/sobre', 'Sobre — Dra. Marina Mariz', 'https://dramarinamariz.com.br/sobre'],
            'especialidades' => ['/especialidades', 'Especialidades — Dra. Marina Mariz', 'https://dramarinamariz.com.br/especialidades'],
            'amara' => ['/amara', 'Amara — Dra. Marina Mariz', 'https://dramarinamariz.com.br/amara'],
            'podcast' => ['/podcast', 'Sem Neura Podcast — Dra. Marina Mariz', 'https://dramarinamariz.com.br/podcast'],
            'contato' => ['/contato', 'Contato — Dra. Marina Mariz', 'https://dramarinamariz.com.br/contato'],
            'newsletter' => ['/newsletter', 'Newsletter — Dra. Marina Mariz', 'https://dramarinamariz.com.br/newsletter'],
            'politica' => ['/politica-de-privacidade', 'Política de Privacidade — Dra. Marina Mariz', 'https://dramarinamariz.com.br/politica-de-privacidade'],
            'termos' => ['/termos-de-uso', 'Termos de Uso — Dra. Marina Mariz', 'https://dramarinamariz.com.br/termos-de-uso'],
        ];
    }

    #[DataProvider('paginas')]
    public function test_pagina_responde_com_titulo_e_canonical(string $url, string $titulo, string $canonical): void
    {
        $resposta = $this->get($url);

        $resposta->assertOk();
        $resposta->assertSee("<title>{$titulo}</title>", false);
        $resposta->assertSee('<link rel="canonical" href="'.$canonical.'">', false);
    }

    public function test_pagina_404_do_site_responde_no_lugar_da_tela_do_laravel(): void
    {
        $resposta = $this->get('/nao-existe');

        $resposta->assertNotFound();
        $resposta->assertSee('Essa página não existe mais.', false);
        // Menu e rodape continuam na pagina: sem eles a pessoa fecha a aba.
        $resposta->assertSee('Navegação principal', false);
        $resposta->assertSee('footer__crm', false);
    }

    public function test_nenhuma_pagina_publica_exige_login(): void
    {
        foreach (self::paginas() as [$url]) {
            $this->get($url)->assertOk();
        }

        $this->get('/nao-existe')->assertNotFound();
    }

    // ── Rodape ───────────────────────────────────────────────────────

    public function test_rodape_traz_os_mesmos_contatos_do_html_de_origem(): void
    {
        $resposta = $this->get('/');

        $resposta->assertSee('<a href="mailto:contato@dramarinamariz.com.br">contato@dramarinamariz.com.br</a>', false);
        $resposta->assertSee('<a href="tel:+553130902320">(31) 3090-2320</a>', false);
        $resposta->assertSee('<a href="https://wa.me/5531996082883" target="_blank" rel="noopener">(31) 99608-2883</a>', false);
        $resposta->assertSee(
            '<address class="footer__addr">R. Cláudio Manoel, 48<br>Sala 1201 — Funcionários<br>Belo Horizonte&nbsp;-&nbsp;MG</address>',
            false
        );
        $resposta->assertSee(
            '<p class="footer__crm"><b>Dra. Marina Mariz — CRM-MG 48.386</b> · Ginecologia e Obstetrícia — RQE 30.992 · Medicina Fetal — RQE 30.993</p>',
            false
        );
    }

    public function test_trocar_o_telefone_no_painel_muda_o_rodape_de_todas_as_paginas(): void
    {
        DadosDoSite::instancia()->update(['telefone' => '3133334444']);

        foreach (self::paginas() as [$url]) {
            $resposta = $this->get($url);
            $resposta->assertSee('<a href="tel:+553133334444">(31) 3333-4444</a>', false);
            $resposta->assertDontSee('(31) 3090-2320', false);
        }
    }

    public function test_rede_desligada_no_painel_some_do_rodape(): void
    {
        $this->get('/')->assertSee('aria-label="Spotify"', false);

        $dados = DadosDoSite::instancia();
        $redes = $dados->redes;
        $redes['spotify']['visivel'] = false;
        $dados->update(['redes' => $redes]);

        $resposta = $this->get('/');
        $resposta->assertDontSee('aria-label="Spotify"', false);
        $resposta->assertDontSee('open.spotify.com', false);
        // As outras quatro continuam, na mesma ordem — sem buraco no lugar.
        $resposta->assertSee(
            '<a href="https://www.youtube.com/@SemNeuraPodcast" target="_blank" rel="noopener" aria-label="YouTube">'
            .'<i class="ph ph-youtube-logo" aria-hidden="true"></i></a>'
            .'<a href="https://www.instagram.com/dramarinamariz" target="_blank" rel="noopener" aria-label="Instagram">'
            .'<i class="ph ph-instagram-logo" aria-hidden="true"></i></a>'
            .'<a href="https://www.linkedin.com/in/marinamariz" target="_blank" rel="noopener" aria-label="LinkedIn">'
            .'<i class="ph ph-linkedin-logo" aria-hidden="true"></i></a>'
            .'<a href="https://wa.me/5531996082883" target="_blank" rel="noopener" aria-label="WhatsApp">'
            .'<i class="ph ph-whatsapp-logo" aria-hidden="true"></i></a>',
            false
        );
    }

    // ── Documentos legais ────────────────────────────────────────────

    public function test_pagina_legal_mostra_a_versao_publicada(): void
    {
        $this->travelTo('2026-08-20 10:00:00');
        $this->publicar('politica-de-privacidade', 'Política de Privacidade', '<p>Texto no ar.</p>');

        $resposta = $this->get('/politica-de-privacidade');
        $resposta->assertSee('Texto no ar.', false);
        $resposta->assertSee('id="doc-titulo">Política de Privacidade</h1>', false);
        // Mesma forma de data do HTML de origem.
        $resposta->assertSee('<time datetime="2026-08-20">20 de agosto de 2026</time>', false);
    }

    public function test_rascunho_salvo_nao_muda_a_pagina_publica_ate_publicar(): void
    {
        $texto = $this->publicar('termos-de-uso', 'Termos de Uso', '<p>Versão no ar.</p>');

        $texto->guardar(['conteudo' => '<p>Versão nova em rascunho.</p>', 'status' => 'rascunho']);
        Site::limparCache();

        $resposta = $this->get('/termos-de-uso');
        $resposta->assertSee('Versão no ar.', false);
        $resposta->assertDontSee('Versão nova em rascunho.', false);

        $texto->guardar(['conteudo' => '<p>Versão nova em rascunho.</p>', 'status' => 'publicado']);
        Site::limparCache();

        $this->get('/termos-de-uso')->assertSee('Versão nova em rascunho.', false);
    }

    public function test_texto_nunca_publicado_nao_aparece_no_site(): void
    {
        TextoLegal::query()->where('chave', 'termos-de-uso')->sole()->guardar([
            'titulo' => 'Termos de Uso',
            'conteudo' => '<p>Ainda em revisão.</p>',
            'status' => 'rascunho',
        ]);
        Site::limparCache();

        $resposta = $this->get('/termos-de-uso');
        $resposta->assertOk();
        $resposta->assertDontSee('Ainda em revisão.', false);
    }

    private function publicar(string $chave, string $titulo, string $conteudo): TextoLegal
    {
        $texto = TextoLegal::query()->where('chave', $chave)->sole();
        $texto->guardar(['titulo' => $titulo, 'conteudo' => $conteudo, 'status' => 'publicado']);
        Site::limparCache();

        return $texto;
    }

    /**
     * A pagina /newsletter e de apoio: o unico caminho ate ela e o link do
     * rodape. Se o link some, a pagina fica inalcancavel — os outros acessos
     * a lista sao os blocos de inscricao espalhados pelo site, nao ela.
     */
    public function test_rodape_leva_a_pagina_da_newsletter(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<li><a href="/newsletter">Newsletter</a></li>', false);

        $this->get('/newsletter')
            ->assertOk()
            ->assertSee('name="origem" value="newsletter"', false);
    }

    public function test_sitemap_lista_as_paginas_fixas_e_so_o_post_no_ar(): void
    {
        $noAr = Post::factory()->create([
            'status' => ContentStatus::Publicado,
            'published_at' => now()->subDay(),
            'slug' => 'esta-no-ar',
        ]);

        Post::factory()->create(['status' => ContentStatus::Rascunho, 'slug' => 'rascunho']);
        Post::factory()->create([
            'status' => ContentStatus::Publicado,
            'published_at' => now()->addWeek(),
            'slug' => 'agendado',
        ]);

        $resposta = $this->get('/sitemap.xml');

        $resposta->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(url('/'), escape: false)
            ->assertSee(route('site.blog.post', $noAr->slug), escape: false)
            ->assertDontSee('/blog/rascunho', escape: false)
            ->assertDontSee('/blog/agendado', escape: false);
    }
}
