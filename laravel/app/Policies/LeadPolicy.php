<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

/**
 * Doc 04: Editor visualiza, filtra e exporta; nenhuma mutacao. Marcar como
 * cliente e excluir do painel sao do Administrador.
 *
 * Registro nasce do formulario do site: ninguem cria lead pelo painel.
 */
class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lead $lead): bool
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

    public function update(User $user, Lead $lead): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->isAdministrator();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function restore(User $user, Lead $lead): bool
    {
        return false;
    }

    public function forceDelete(User $user, Lead $lead): bool
    {
        return false;
    }
}
