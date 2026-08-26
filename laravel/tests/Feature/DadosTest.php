<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\ContatosEDados;
use App\Models\TextoLegal;
use App\Models\User;
use App\Services\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Contatos e dados (doc 03): fonte unica do site, so do Administrador.
 */
class DadosTest extends TestCase
{
    use RefreshDatabase;

    /** Sem TOTP o painel intercepta antes da autorizacao (ver UsuariosTest). */
    private function comTotp(UserRole $papel): User
    {
        $user = User::factory()->create(['role' => $papel]);
        $user->saveAppAuthenticationSecret('BASE32SECRETOTESTE');

        return $user;
    }

    private function admin(): User
    {
        return $this->comTotp(UserRole::Administrator);
    }

    private function editor(): User
    {
        return $this->comTotp(UserRole::Editor);
    }

    /** @return array<string, mixed> */
    private function formularioValido(array $sobrescreve = []): array
    {
        return [
            'email' => 'contato@dramarinamariz.com.br',
            'telefone' => '3130902320',
            'whatsapp' => '31996082883',
            'horario_atendimento' => 'Segunda a Sexta, 8h às 18h',
            'nome_divulgacao' => 'Marina Mariz',
            'palavra_obrigatoria' => 'MÉDICO',
            'crm' => 'CRM-MG 48.386',
            'especialidades' => [
                ['especialidade' => 'Ginecologia e Obstetrícia', 'rqe' => '12345'],
            ],
            'endereco_logradouro' => 'R. Cláudio Manoel, 48',
            'endereco_complemento' => 'Sala 1201',
            'endereco_bairro' => 'Funcionários',
            'endereco_cidade' => 'Belo Horizonte',
            'endereco_estado' => 'MG',
            'endereco_cep' => '30140100',
            'redes' => [
                'instagram' => ['url' => 'https://www.instagram.com/dramarinamariz', 'visivel' => true],
                'youtube' => ['url' => 'https://www.youtube.com/@SemNeuraPodcast', 'visivel' => false],
                'linkedin' => ['url' => 'https://www.linkedin.com/in/marinamariz', 'visivel' => true],
                'spotify' => ['url' => 'https://open.spotify.com/show/semneurapodcast', 'visivel' => true],
                'whatsapp' => ['visivel' => true],
                'comunidade' => ['url' => 'https://comunidade.dramarinamariz.com.br', 'visivel' => true],
            ],
            'textos' => [
                'politica-de-privacidade' => ['titulo' => 'Política de Privacidade', 'conteudo' => null, 'status' => 'rascunho'],
                'termos-de-uso' => ['titulo' => 'Termos de Uso', 'conteudo' => null, 'status' => 'rascunho'],
            ],
            ...$sobrescreve,
        ];
    }

    // ── Acesso ───────────────────────────────────────────────────────

    public function test_editor_e_barrado_na_rota_de_contatos_e_dados(): void
    {
        $this->actingAs($this->editor())
            ->get(ContatosEDados::getUrl())
            ->assertForbidden();
    }

    public function test_menu_de_contatos_e_dados_some_para_o_editor(): void
    {
        $this->actingAs($this->editor());
        $this->assertFalse(ContatosEDados::canAccess());

        $this->actingAs($this->admin());
        $this->assertTrue(ContatosEDados::canAccess());
    }

    public function test_editor_nao_salva_nem_chamando_a_acao_direto(): void
    {
        $this->actingAs($this->editor());

        // hydrate da pagina Livewire ja devolve 403 ao Editor.
        Livewire::test(ContatosEDados::class)->assertForbidden();
    }

    public function test_administrador_abre_a_tela(): void
    {
        $this->actingAs($this->admin())
            ->get(ContatosEDados::getUrl())
            ->assertOk();
    }

    // ── Gravacao ─────────────────────────────────────────────────────

    public function test_administrador_salva_e_o_valor_persiste(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido(['telefone' => '3133334444']))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('dados_do_site', ['telefone' => '3133334444']);
    }

    public function test_salvar_invalida_o_cache(): void
    {
        $this->actingAs($this->admin());

        // Esquenta o cache com o valor antigo antes de gravar o novo.
        $this->assertSame('3130902320', Site::dados()->telefone);

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido(['telefone' => '3199990000']))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('3199990000', Site::dados()->telefone);
        $this->assertSame('(31) 9999-0000', Site::telefoneFormatado());
    }

    // ── Validacao no servidor ────────────────────────────────────────

    public function test_campos_obrigatorios_do_doc_03_sao_exigidos(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido([
                'email' => null,
                'telefone' => null,
                'whatsapp' => null,
                'nome_divulgacao' => null,
                'endereco_logradouro' => null,
                'endereco_bairro' => null,
                'endereco_cidade' => null,
                'endereco_estado' => null,
                'endereco_cep' => null,
            ]))
            ->call('save')
            ->assertHasFormErrors([
                'email',
                'telefone',
                'whatsapp',
                'nome_divulgacao',
                'endereco_logradouro',
                'endereco_bairro',
                'endereco_cidade',
                'endereco_estado',
                'endereco_cep',
            ]);
    }

    public function test_palavra_obrigatoria_e_registro_do_cfm_aparecem_e_sao_exigidos(): void
    {
        $this->actingAs($this->admin());

        $campos = array_keys(
            Livewire::test(ContatosEDados::class)->instance()->form->getFlatFields()
        );

        $this->assertContains('palavra_obrigatoria', $campos);
        $this->assertContains('crm', $campos);

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido([
                'palavra_obrigatoria' => null,
                'crm' => null,
                'especialidades' => [],
            ]))
            ->call('save')
            ->assertHasFormErrors(['palavra_obrigatoria', 'crm', 'especialidades']);
    }

    public function test_rqe_de_cada_especialidade_e_exigido(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido([
                'especialidades' => [
                    ['especialidade' => 'Ginecologia e Obstetrícia', 'rqe' => ''],
                ],
            ]))
            ->call('save')
            ->assertHasFormErrors();
    }

    public function test_telefone_com_pontuacao_e_recusado(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido(['telefone' => '(31) 3090-2320']))
            ->call('save')
            ->assertHasFormErrors(['telefone']);
    }

    public function test_rede_social_sem_https_e_recusada(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido([
                'redes' => [
                    ...$this->formularioValido()['redes'],
                    'instagram' => ['url' => 'http://instagram.com/dramarinamariz', 'visivel' => true],
                ],
            ]))
            ->call('save')
            ->assertHasFormErrors();
    }

    // ── Fonte unica ──────────────────────────────────────────────────

    public function test_links_de_contato_saem_montados_do_service(): void
    {
        $this->assertSame('tel:+553130902320', Site::linkTel());
        $this->assertSame('https://wa.me/5531996082883', Site::linkWhatsapp());
        $this->assertSame('(31) 99608-2883', Site::whatsappFormatado());
        $this->assertSame('Marina Mariz — MÉDICO — CRM-MG 48.386', Site::identificacaoProfissional());
    }

    public function test_rede_oculta_some_do_site_sem_perder_o_link(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido())
            ->call('save')
            ->assertHasNoFormErrors();

        $visiveis = Site::redesVisiveis();

        $this->assertArrayNotHasKey('youtube', $visiveis);
        $this->assertArrayHasKey('instagram', $visiveis);
        // O link do YouTube continua guardado, so nao e exibido.
        $this->assertSame(
            'https://www.youtube.com/@SemNeuraPodcast',
            Site::dados()->redes['youtube']['url']
        );
        // WhatsApp usa o numero do bloco Contato, sem URL propria.
        $this->assertSame('https://wa.me/5531996082883', $visiveis['whatsapp']['url']);
    }

    // ── Textos legais ────────────────────────────────────────────────

    public function test_textos_legais_ficam_no_banco_e_nascem_como_rascunho(): void
    {
        $this->assertDatabaseHas('textos_legais', ['chave' => 'politica-de-privacidade']);
        $this->assertDatabaseHas('textos_legais', ['chave' => 'termos-de-uso']);
        $this->assertNull(Site::textoLegal('politica-de-privacidade'));
    }

    public function test_rascunho_nao_muda_a_pagina_publica_e_publicar_muda(): void
    {
        $this->actingAs($this->admin());

        $textos = $this->formularioValido()['textos'];
        $textos['politica-de-privacidade'] = [
            'titulo' => 'Política de Privacidade',
            'conteudo' => '<p>Rascunho ainda não revisado.</p>',
            'status' => 'rascunho',
        ];

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido(['textos' => $textos]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull(Site::textoLegal('politica-de-privacidade'));

        $textos['politica-de-privacidade']['conteudo'] = '<p>Texto valendo.</p>';
        $textos['politica-de-privacidade']['status'] = 'publicado';

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido(['textos' => $textos]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertStringContainsString('Texto valendo.', Site::textoLegal('politica-de-privacidade')['conteudo']);
    }

    public function test_publicar_sem_conteudo_e_recusado(): void
    {
        $this->actingAs($this->admin());

        $textos = $this->formularioValido()['textos'];
        $textos['termos-de-uso']['status'] = 'publicado';

        Livewire::test(ContatosEDados::class)
            ->fillForm($this->formularioValido(['textos' => $textos]))
            ->call('save')
            ->assertHasFormErrors();
    }

    public function test_variaveis_do_sistema_entram_no_texto_publicado(): void
    {
        TextoLegal::query()->where('chave', 'termos-de-uso')->sole()->guardar([
            'titulo' => 'Termos de Uso',
            'conteudo' => '<p>Responsável: <span data-type="mergeTag" data-id="identificacao_profissional">identificacao_profissional</span>.</p>',
            'status' => 'publicado',
        ]);

        $this->assertStringContainsString(
            'Marina Mariz — MÉDICO — CRM-MG 48.386',
            Site::textoLegal('termos-de-uso')['conteudo']
        );
    }

    public function test_o_indice_da_pagina_legal_vem_do_texto_do_painel(): void
    {
        TextoLegal::query()->where('chave', 'termos-de-uso')->sole()->guardar([
            'titulo' => 'Termos de Uso',
            'conteudo' => '<h2>1. Sobre estes Termos</h2><p>Texto.</p><h2>2. Contato</h2><p>Fale.</p>',
            'status' => 'publicado',
        ]);

        $html = $this->get('/termos-de-uso')->getContent();

        // O sumario e as ancoras saem do mesmo texto: renomear a secao no
        // painel renomeia no indice, sem link apontando para id inexistente.
        $this->assertStringContainsString('<h2 id="1-sobre-estes-termos"', (string) $html);
        $this->assertStringContainsString('<a href="#1-sobre-estes-termos">1. Sobre estes Termos</a>', (string) $html);
        $this->assertStringContainsString('<a href="#2-contato">2. Contato</a>', (string) $html);
        $this->assertStringNotContainsString('href="#sobre"', (string) $html);
    }
}
