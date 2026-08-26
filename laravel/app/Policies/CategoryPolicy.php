<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * Tela só do Administrador. O Editor escolhe categorias já existentes ao
 * editar um post, mas não administra a lista (doc 02).
 */
class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function view(User $user, Category $category): bool
    {
        return $user->isAdministrator();
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isAdministrator();
    }

    /** Arrastar para reordenar as abas do site. */
    public function reorder(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->isAdministrator();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    // Categoria não usa SoftDeletes: excluir é definitivo e os posts caem
    // em "Sem categoria". Os métodos existem porque o painel roda com
    // strictAuthorization e cobra a Policy inteira.

    public function restore(User $user, Category $category): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
