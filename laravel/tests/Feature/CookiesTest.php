<?php

namespace Tests\Feature;

use App\Models\Configuracao;
use App\Support\AvisoDeCookies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Aviso de Cookies: sem escolha, nenhum script de rastreamento existe no
 * HTML. Recusar precisa valer de verdade, nao so esconder a faixa.
 */
class CookiesTest extends TestCase
{
    use RefreshDatabase;

    private const IP_CHAVE = 'chave-de-teste';

    protected function setUp(): void
    {
        parent::setUp();

        config(['pessoas.ip_hash_key' => self::IP_CHAVE]);

        // O site publico ainda esta sendo montado por outra frente; esta
        // rota exercita exatamente os dois parciais que este modulo entrega.
        Route::middleware('web')->get('/pagina-de-teste', fn () => Blade::render(
            '@include("site.analytics")@include("site.partials.aviso-de-cookies")'
        ));
    }

    private function comIdsCadastrados(array $ids = []): void
    {
        Configuracao::instancia()->update($ids + [
            'google_analytics_id' => 'G-TESTE123',
            'meta_pixel_id' => '11112222333344',
        ]);
    }

    /** @param array{analiticos: bool, marketing: bool} $escolha */
    private function comEscolha(array $escolha, ?string $versao = null): static
    {
        return $this->withUnencryptedCookie(AvisoDeCookies::COOKIE, json_encode([
            'versao' => $versao ?? AvisoDeCookies::VERSAO,
            'analiticos' => $escolha['analiticos'],
            'marketing' => $escolha['marketing'],
            'em' => now()->toIso8601String(),
        ]));
    }

    private function assertSemRastreamento(TestResponse $resposta): void
    {
        foreach (['G-TESTE123', 'GTM-TESTE', '11112222333344', 'googletagmanager.com', 'fbevents.js'] as $rastro) {
            $resposta->assertDontSee($rastro, escape: false);
        }
    }

    // ── Sem escolha ──────────────────────────────────────────────────

    public function test_sem_escolha_nenhuma_tag_de_rastreamento_entra_no_html(): void
    {
        $this->comIdsCadastrados();

        // Nem no HTML, nem carregada depois: nao ha script algum na pagina.
        $this->assertSemRastreamento($this->get('/pagina-de-teste')->assertOk());
    }

    public function test_sem_escolha_o_aviso_aparece(): void
    {
        $this->get('/pagina-de-teste')
            ->assertSee('id="aviso-de-cookies"', escape: false)
            ->assertSee('Aceitar tudo')
            ->assertSee('Recusar')
            ->assertSee('Personalizar')
            ->assertDontSee('aria-live="polite" hidden>', escape: false);
    }

    public function test_cookies_opcionais_nao_nascem_marcados(): void
    {
        $html = $this->get('/pagina-de-teste')->assertOk()->getContent();

        $this->assertStringNotContainsString(' checked', $html);
        $this->assertSame(2, substr_count($html, 'type="checkbox"'));
    }

    // ── Com escolha ──────────────────────────────────────────────────

    public function test_com_consentimento_as_tags_entram(): void
    {
        $this->comIdsCadastrados();

        $resposta = $this->comEscolha(['analiticos' => true, 'marketing' => true])
            ->get('/pagina-de-teste');

        $resposta->assertSee('G-TESTE123', escape: false);
        $resposta->assertSee('11112222333344', escape: false);
    }

    public function test_emite_apenas_o_que_tem_id_cadastrado(): void
    {
        // Meta Pixel sem ID: consentimento de marketing nao inventa script.
        $this->comIdsCadastrados(['meta_pixel_id' => null]);

        $resposta = $this->comEscolha(['analiticos' => true, 'marketing' => true])
            ->get('/pagina-de-teste');

        $resposta->assertSee('G-TESTE123', escape: false);
        $resposta->assertDontSee('fbevents.js', escape: false);
    }

    public function test_cada_categoria_controla_so_o_que_e_dela(): void
    {
        $this->comIdsCadastrados();

        // Analiticos sim, marketing nao: entra o Google, nao entra a Meta.
        $resposta = $this->comEscolha(['analiticos' => true, 'marketing' => false])
            ->get('/pagina-de-teste');

        $resposta->assertSee('G-TESTE123', escape: false);
        $resposta->assertDontSee('fbevents.js', escape: false);
        // E o container do Google recebe a recusa de marketing, para uma
        // tag interna dele nao furar a escolha.
        $resposta->assertSee('ad_storage: \'denied\'', escape: false);

        // E ao contrario.
        $resposta = $this->comEscolha(['analiticos' => false, 'marketing' => true])
            ->get('/pagina-de-teste');

        $resposta->assertDontSee('G-TESTE123', escape: false);
        $resposta->assertSee('11112222333344', escape: false);
    }

    // ── Recusa ───────────────────────────────────────────────────────

    public function test_recusa_e_respeitada_e_persiste_entre_requisicoes(): void
    {
        $this->comIdsCadastrados();

        $resposta = $this->from('/pagina-de-teste')
            ->post('/preferencias-de-cookies', ['escolha' => 'recusar'])
            ->assertRedirect('/pagina-de-teste');

        $cookie = $resposta->getCookie(AvisoDeCookies::COOKIE, false);
        $this->assertNotNull($cookie);

        // O navegador volta com o cookie da recusa; nada de rastreamento
        // entra agora nem nas proximas paginas.
        foreach ([1, 2] as $ignorado) {
            $this->assertSemRastreamento(
                $this->withUnencryptedCookie(AvisoDeCookies::COOKIE, $cookie->getValue())
                    ->get('/pagina-de-teste')
            );
        }
    }

    // ── Registro do consentimento ────────────────────────────────────

    public function test_registro_guarda_versao_data_hora_e_ip_pseudonimizado(): void
    {
        $this->post('/preferencias-de-cookies', ['escolha' => 'aceitar'])->assertRedirect();

        $registro = DB::table('consentimentos_de_cookies')->sole();

        $this->assertSame(AvisoDeCookies::VERSAO, $registro->versao_aviso);
        $this->assertEquals(1, $registro->analiticos);
        $this->assertEquals(1, $registro->marketing);
        $this->assertNotNull($registro->decidido_em);
        $this->assertSame(hash_hmac('sha256', '127.0.0.1', self::IP_CHAVE), $registro->ip_hash);

        // O IP em claro nao pode estar em coluna nenhuma.
        foreach ((array) $registro as $valor) {
            $this->assertStringNotContainsString('127.0.0.1', (string) $valor);
        }
    }

    public function test_sem_chave_de_hmac_nao_se_grava_ip_algum(): void
    {
        config(['pessoas.ip_hash_key' => null]);

        $this->post('/preferencias-de-cookies', ['escolha' => 'recusar']);

        $this->assertNull(DB::table('consentimentos_de_cookies')->sole()->ip_hash);
    }

    public function test_a_recusa_tambem_fica_registrada(): void
    {
        $this->post('/preferencias-de-cookies', ['escolha' => 'recusar']);

        $registro = DB::table('consentimentos_de_cookies')->sole();

        $this->assertEquals(0, $registro->analiticos);
        $this->assertEquals(0, $registro->marketing);
    }

    // ── Mudar de ideia ───────────────────────────────────────────────

    public function test_mudar_de_ideia_funciona_nos_dois_sentidos(): void
    {
        $this->comIdsCadastrados();

        // Recusou, depois aceitou.
        $cookie = $this->post('/preferencias-de-cookies', ['escolha' => 'recusar'])
            ->getCookie(AvisoDeCookies::COOKIE, false)->getValue();
        $this->assertSemRastreamento(
            $this->withUnencryptedCookie(AvisoDeCookies::COOKIE, $cookie)->get('/pagina-de-teste')
        );

        $cookie = $this->post('/preferencias-de-cookies', ['escolha' => 'aceitar'])
            ->getCookie(AvisoDeCookies::COOKIE, false)->getValue();
        $this->withUnencryptedCookie(AvisoDeCookies::COOKIE, $cookie)
            ->get('/pagina-de-teste')
            ->assertSee('G-TESTE123', escape: false);

        // Aceitou, depois revogou pelo painel de preferencias.
        $cookie = $this->post('/preferencias-de-cookies', ['escolha' => 'salvar'])
            ->getCookie(AvisoDeCookies::COOKIE, false)->getValue();
        $this->assertSemRastreamento(
            $this->withUnencryptedCookie(AvisoDeCookies::COOKIE, $cookie)->get('/pagina-de-teste')
        );

        // Cada mudanca deixa a sua propria prova.
        $this->assertSame(3, DB::table('consentimentos_de_cookies')->count());
    }

    public function test_salvar_preferencias_liga_so_a_categoria_marcada(): void
    {
        $this->comIdsCadastrados();

        $cookie = $this->post('/preferencias-de-cookies', ['escolha' => 'salvar', 'analiticos' => '1'])
            ->getCookie(AvisoDeCookies::COOKIE, false)->getValue();

        $resposta = $this->withUnencryptedCookie(AvisoDeCookies::COOKIE, $cookie)->get('/pagina-de-teste');
        $resposta->assertSee('G-TESTE123', escape: false);
        $resposta->assertDontSee('fbevents.js', escape: false);
    }

    public function test_escolha_invalida_e_recusada(): void
    {
        $this->post('/preferencias-de-cookies', ['escolha' => 'talvez'])
            ->assertSessionHasErrors('escolha');

        $this->assertSame(0, DB::table('consentimentos_de_cookies')->count());
    }

    // ── Reaparecimento e prazo ───────────────────────────────────────

    public function test_o_aviso_nao_aparece_para_quem_ja_decidiu(): void
    {
        $this->comEscolha(['analiticos' => false, 'marketing' => false])
            ->get('/pagina-de-teste')
            // A faixa continua no HTML, oculta, para o link "Preferencias
            // de cookies" do rodape reabri-la sem JavaScript.
            ->assertSee('aria-live="polite" hidden>', escape: false)
            ->assertSee('Fechar sem alterar');
    }

    public function test_o_aviso_reaparece_quando_a_versao_do_aviso_muda(): void
    {
        $this->comIdsCadastrados();

        $resposta = $this->comEscolha(['analiticos' => true, 'marketing' => true], versao: 'versao-antiga')
            ->get('/pagina-de-teste');

        // Escolha velha nao vale: o aviso volta e o rastreamento sai.
        $resposta->assertDontSee('aria-live="polite" hidden>', escape: false);
        $this->assertSemRastreamento($resposta);
    }

    public function test_o_cookie_dura_doze_meses_e_a_interface_consegue_le_lo(): void
    {
        $cookie = $this->post('/preferencias-de-cookies', ['escolha' => 'aceitar'])
            ->getCookie(AvisoDeCookies::COOKIE, false);

        $this->assertEqualsWithDelta(
            now()->addMinutes(AvisoDeCookies::VALIDADE)->getTimestamp(),
            $cookie->getExpiresTime(),
            60,
        );

        $this->assertSame('/', $cookie->getPath());
        $this->assertSame('lax', $cookie->getSameSite());
        // Sem HttpOnly de proposito, e sem cifra: a interface de
        // preferencias precisa ler o valor.
        $this->assertFalse($cookie->isHttpOnly());
        $this->assertSame(AvisoDeCookies::VERSAO, json_decode($cookie->getValue(), true)['versao']);
    }
}
