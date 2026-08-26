<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/**
 * Editor cria, edita, visualiza e publica. Arquivar, desarquivar, lixeira,
 * restaurar e excluir definitivamente são do Administrador (doc 00).
 */
class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Post $post): bool
    {
        return true;
    }

    /** Arrastar para reordenar a lista. */
    public function reorder(User $user): bool
    {
        return true;
    }

    /** Arquivar e desarquivar. Não é método padrão do Filament; o model também chama. */
    public function arquivar(User $user, Post $post): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isAdministrator();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->isAdministrator();
    }

    public function restoreAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->isAdministrator();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }
}
