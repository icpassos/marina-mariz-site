<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Porta de entrada do painel: quem entra, quem nao entra, e se o TOTP
 * e mesmo obrigatorio. Se algum destes quebrar, nenhum modulo acima
 * vale nada.
 */
class PainelAcessoTest extends TestCase
{
    use RefreshDatabase;

    public function test_painel_responde_no_endereco_da_spec(): void
    {
        $this->get('/paineladm/login')->assertOk();
        $this->get('/admin')->assertNotFound();
    }

    public function test_visitante_e_mandado_para_o_login(): void
    {
        $this->get('/paineladm')->assertRedirect('/paineladm/login');
    }

    public function test_administrador_e_editor_acessam_o_painel(): void
    {
        foreach ([UserRole::Administrator, UserRole::Editor] as $papel) {
            $user = User::factory()->create(['role' => $papel]);

            $this->assertTrue($user->canAccessPanel(filament()->getPanel('admin')));
        }
    }

    public function test_banco_recusa_papel_invalido(): void
    {
        $user = User::factory()->create();

        $this->expectException(QueryException::class);

        \DB::table('users')->where('id', $user->id)->update(['role' => 'visitante']);
    }

    public function test_totp_e_obrigatorio_no_painel(): void
    {
        $painel = filament()->getPanel('admin');

        $this->assertTrue($painel->hasMultiFactorAuthentication());
        $this->assertTrue($painel->isMultiFactorAuthenticationRequired());
    }

    public function test_segredo_do_totp_nunca_sai_em_serializacao(): void
    {
        $user = User::factory()->create(['role' => UserRole::Administrator]);
        $user->saveAppAuthenticationSecret('SEGREDOTOTP123456');

        $exposto = $user->fresh()->toArray();

        $this->assertArrayNotHasKey('app_authentication_secret', $exposto);
        $this->assertArrayNotHasKey('app_authentication_recovery_codes', $exposto);
        $this->assertArrayNotHasKey('password', $exposto);
    }

    public function test_segredo_do_totp_fica_criptografado_no_banco(): void
    {
        $user = User::factory()->create(['role' => UserRole::Administrator]);
        $user->saveAppAuthenticationSecret('SEGREDOTOTP123456');

        $bruto = \DB::table('users')->where('id', $user->id)->value('app_authentication_secret');

        $this->assertNotSame('SEGREDOTOTP123456', $bruto);
        $this->assertSame('SEGREDOTOTP123456', $user->fresh()->getAppAuthenticationSecret());
    }
}
