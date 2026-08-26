<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\Configuracoes;
use App\Models\User;
use App\Services\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Configuracoes (doc 06): SEO padrao e IDs de Analytics, so do
 * Administrador.
 */
class ConfiguracoesTest extends TestCase
{
    use RefreshDatabase;

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
            'seo_title' => 'Dra. Marina Mariz — Ginecologia e Obstetrícia',
            'seo_description' => 'Acompanhamento em ginecologia e obstetrícia em Belo Horizonte.',
            'seo_image_id' => null,
            'google_analytics_id' => null,
            'google_tag_manager_id' => null,
            'meta_pixel_id' => null,
            ...$sobrescreve,
        ];
    }

    private function analytics(bool $permitido = true): string
    {
        return view('site.analytics', [
            'analyticsPermitido' => $permitido,
            // Meta Pixel e a categoria "marketing" do Aviso de Cookies,
            // separada dos analiticos.
            'marketingPermitido' => $permitido,
        ])->render();
    }

    // ── Acesso ───────────────────────────────────────────────────────

    public function test_editor_e_barrado_na_rota_de_configuracoes(): void
    {
        $this->actingAs($this->editor())
            ->get(Configuracoes::getUrl())
            ->assertForbidden();
    }

    public function test_editor_e_barrado_na_pagina_livewire(): void
    {
        $this->actingAs($this->editor());

        Livewire::test(Configuracoes::class)->assertForbidden();
    }

    public function test_menu_de_configuracoes_some_para_o_editor(): void
    {
        $this->actingAs($this->editor());
        $this->assertFalse(Configuracoes::canAccess());

        $this->actingAs($this->admin());
        $this->assertTrue(Configuracoes::canAccess());
    }

    public function test_administrador_abre_a_tela(): void
    {
        $this->actingAs($this->admin())
            ->get(Configuracoes::getUrl())
            ->assertOk();
    }

    // ── Gravacao ─────────────────────────────────────────────────────

    public function test_administrador_salva_e_o_valor_persiste(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(Configuracoes::class)
            ->fillForm($this->formularioValido(['google_analytics_id' => 'G-ABC123']))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('configuracoes', ['google_analytics_id' => 'G-ABC123']);
    }

    public function test_salvar_invalida_o_cache(): void
    {
        $this->actingAs($this->admin());

        // Cache esquentado com o valor antigo.
        $this->assertNull(Site::configuracoes()->google_analytics_id);

        Livewire::test(Configuracoes::class)
            ->fillForm($this->formularioValido(['google_analytics_id' => 'G-NOVO999']))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('G-NOVO999', Site::configuracoes()->google_analytics_id);
    }

    public function test_titulo_e_descricao_padrao_sao_exigidos(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(Configuracoes::class)
            ->fillForm($this->formularioValido(['seo_title' => null, 'seo_description' => null]))
            ->call('save')
            ->assertHasFormErrors(['seo_title', 'seo_description']);
    }

    public function test_analytics_e_tag_manager_nao_ficam_ativos_ao_mesmo_tempo(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(Configuracoes::class)
            ->fillForm($this->formularioValido([
                'google_analytics_id' => 'G-ABC123',
                'google_tag_manager_id' => 'GTM-XYZ789',
            ]))
            ->call('save')
            ->assertHasFormErrors(['google_analytics_id']);

        $this->assertDatabaseMissing('configuracoes', ['google_tag_manager_id' => 'GTM-XYZ789']);
    }

    // ── Emissao dos scripts ──────────────────────────────────────────

    public function test_id_em_branco_nao_emite_script(): void
    {
        $html = $this->analytics();

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('googletagmanager', $html);
        $this->assertStringNotContainsString('fbevents', $html);
    }

    public function test_id_preenchido_emite_script(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(Configuracoes::class)
            ->fillForm($this->formularioValido([
                'google_analytics_id' => 'G-ABC123',
                'meta_pixel_id' => '1234567890',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $html = $this->analytics();

        $this->assertStringContainsString('G-ABC123', $html);
        $this->assertStringContainsString('1234567890', $html);
        // GTM ficou vazio: nada dele sai.
        $this->assertStringNotContainsString('gtm.js', $html);
    }

    public function test_sem_consentimento_nada_e_emitido(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(Configuracoes::class)
            ->fillForm($this->formularioValido(['google_analytics_id' => 'G-ABC123']))
            ->call('save');

        $this->assertStringNotContainsString('G-ABC123', $this->analytics(permitido: false));
    }
}
