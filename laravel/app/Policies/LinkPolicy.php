<?php

namespace App\Policies;

use App\Models\Link;
use App\Models\User;

/**
 * Mesma divisao dos outros conteudos: Editor cria, edita, ordena e
 * publica; arquivar e mexer na lixeira e do Administrador (docs 00 e 09).
 */
class LinkPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Link $link): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Link $link): bool
    {
        return true;
    }

    public function reorder(User $user): bool
    {
        return true;
    }

    public function arquivar(User $user, ?Link $link = null): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, Link $link): bool
    {
        return $user->isAdministrator();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function restore(User $user, Link $link): bool
    {
        return $user->isAdministrator();
    }

    public function restoreAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function forceDelete(User $user, Link $link): bool
    {
        return $user->isAdministrator();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }
}
