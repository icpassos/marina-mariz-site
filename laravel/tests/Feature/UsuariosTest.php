<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Usuarios e area exclusiva do Administrador (doc 07). O Editor nao
 * alcanca nem a rota, nem a acao.
 */
class UsuariosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Usuario com TOTP ja configurado. Sem isso o painel intercepta a
     * requisicao antes da autorizacao e manda configurar o 2FA, e o
     * teste mediria o middleware em vez da Policy.
     */
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

    public function test_sem_totp_configurado_o_painel_manda_configurar(): void
    {
        $semTotp = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($semTotp)
            ->get(UserResource::getUrl('index'))
            ->assertRedirect();
    }

    public function test_administrador_abre_a_lista_de_usuarios(): void
    {
        $this->actingAs($this->admin())
            ->get(UserResource::getUrl('index'))
            ->assertOk();
    }

    public function test_editor_e_barrado_na_lista_de_usuarios(): void
    {
        $this->actingAs($this->editor())
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_editor_e_barrado_ao_criar_usuario(): void
    {
        $this->actingAs($this->editor())
            ->get(UserResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_menu_de_usuarios_some_para_o_editor(): void
    {
        $this->actingAs($this->editor());
        $this->assertFalse(UserResource::canViewAny());

        $this->actingAs($this->admin());
        $this->assertTrue(UserResource::canViewAny());
    }

    public function test_formulario_nunca_expoe_os_campos_de_totp(): void
    {
        $this->actingAs($this->admin());

        $campos = array_keys(
            Livewire::test(CreateUser::class)->instance()->form->getFlatFields()
        );

        $this->assertContains('name', $campos);
        $this->assertNotContains('app_authentication_secret', $campos);
        $this->assertNotContains('app_authentication_recovery_codes', $campos);
    }

    public function test_senha_curta_e_recusada(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Marina Mariz',
                'email' => 'marina@exemplo.com.br',
                'role' => UserRole::Editor->value,
                'password' => 'curta123',
                'passwordConfirmation' => 'curta123',
            ])
            ->call('create')
            ->assertHasFormErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'marina@exemplo.com.br']);
    }

    public function test_confirmacao_divergente_e_recusada(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Marina Mariz',
                'email' => 'marina@exemplo.com.br',
                'role' => UserRole::Editor->value,
                'password' => 'senhaquepassados12',
                'passwordConfirmation' => 'outracoisadiferente',
            ])
            ->call('create')
            ->assertHasFormErrors(['password']);
    }

    public function test_administrador_cria_usuario_com_senha_em_hash(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Marina Mariz',
                'email' => 'marina@exemplo.com.br',
                'role' => UserRole::Editor->value,
                'password' => 'senhaquepassados12',
                'passwordConfirmation' => 'senhaquepassados12',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $novo = User::where('email', 'marina@exemplo.com.br')->sole();

        $this->assertSame(UserRole::Editor, $novo->role);
        $this->assertNotSame('senhaquepassados12', $novo->getAuthPassword());
        $this->assertTrue(Hash::check('senhaquepassados12', $novo->getAuthPassword()));
    }
}
