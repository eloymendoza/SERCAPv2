<?php

namespace App\Domain\Puestos\Policies;

use App\Domain\Autenticacion\Models\User;

class PuestoPolicy
{
    /**
     * Determina si el usuario puede ver la lista de puestos.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver un puesto específico.
     */
    public function view(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede crear un puesto.
     */
    public function create(User $user): bool
    {
        return $user->can('ERS');
    }

    /**
     * Determina si el usuario puede actualizar un puesto.
     */
    public function update(User $user): bool
    {
        return $user->can('ERS');
    }

    /**
     * Determina si el usuario puede eliminar un puesto.
     */
    public function delete(User $user): bool
    {
        return $user->can('ERS');
    }
}