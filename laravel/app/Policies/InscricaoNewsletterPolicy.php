<?php

namespace App\Policies;

use App\Models\InscricaoNewsletter;
use App\Models\User;

/**
 * Doc 04: Editor visualiza, filtra e exporta; nenhuma mutacao. Excluir do painel e
 * do Administrador.
 *
 * Registro nasce do formulario do site: ninguem cria inscricao pelo painel.
 */
class InscricaoNewsletterPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, InscricaoNewsletter $inscricao): bool
    {
        return true;
    }

    /** Administrador e Editor exportam a consulta filtrada (doc 08). */
    public function export(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, InscricaoNewsletter $inscricao): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, InscricaoNewsletter $inscricao): bool
    {
        return $user->isAdministrator();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function restore(User $user, InscricaoNewsletter $inscricao): bool
    {
        return false;
    }

    public function forceDelete(User $user, InscricaoNewsletter $inscricao): bool
    {
        return false;
    }
}
