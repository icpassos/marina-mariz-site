<?php

namespace App\Policies;

use App\Models\EducationItem;
use App\Models\User;

/**
 * Editor cria, edita, ordena e publica. Arquivar, enviar a lixeira,
 * restaurar e excluir definitivamente sao do Administrador (docs 00 e 01).
 */
class EducationItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EducationItem $item): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, EducationItem $item): bool
    {
        return true;
    }

    public function reorder(User $user): bool
    {
        return true;
    }

    /** Arquivar tira o item do ar; e a mesma familia de acoes do lixo. */
    public function arquivar(User $user, ?EducationItem $item = null): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, EducationItem $item): bool
    {
        return $user->isAdministrator();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function restore(User $user, EducationItem $item): bool
    {
        return $user->isAdministrator();
    }

    public function restoreAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function forceDelete(User $user, EducationItem $item): bool
    {
        return $user->isAdministrator();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }
}
