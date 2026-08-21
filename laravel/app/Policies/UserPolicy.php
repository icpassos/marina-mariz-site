<?php

namespace App\Policies;

use App\Models\User;

/**
 * Usuarios sao area exclusiva do Administrador. O Editor nao ve o menu
 * nem alcanca as rotas: cada acao e negada aqui, no servidor.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdministrator();
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdministrator();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    // Usuario nao usa SoftDeletes: exclusao e definitiva, sem lixeira.
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
