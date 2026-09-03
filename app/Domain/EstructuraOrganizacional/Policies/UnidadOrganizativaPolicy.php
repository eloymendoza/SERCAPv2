<?php

namespace App\Domain\EstructuraOrganizacional\Policies;

use App\Domain\Autenticacion\Models\User;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

use App\Domain\EstructuraOrganizacional\Enums\UnidadOrganizativaEstadoEnum;

class UnidadOrganizativaPolicy
{
    /**
     * Valida si el usuario tiene permiso explícito o es jefe de Talento y Cultura Organizacional (232).
     */
    private function isAuthorized(User $user): bool
    {
        if ($user->can('GTCO')) {
            return true;
        }

        return UnidadOrganizativa::where('id', 232)
            ->where('estado', UnidadOrganizativaEstadoEnum::ACTIVO->value)
            ->where(function ($query) use ($user) {
                $query->where('encargado_id', $user->id_personal)
                      ->orWhere('encargado_usuario', $user->username);
            })
            ->exists();
    }

    /**
     * Determina si el usuario puede crear una unidad organizativa.
     */
    public function create(User $user): bool
    {
        return $this->isAuthorized($user);
    }

    /**
     * Determina si el usuario puede modificar una unidad organizativa.
     */
    public function update(User $user, UnidadOrganizativa $unidad): bool
    {
        return $this->isAuthorized($user);
    }

    /**
     * Determina si el usuario puede eliminar una unidad organizativa.
     */
    public function delete(User $user): bool
    {
        // Nota: en el request original te faltó inyectar el modelo en la firma, lo corrijo aquí.
        return $this->isAuthorized($user);
    }

    /**
     * Determina si el usuario puede activar una unidad organizativa.
     */
    public function activate(User $user, UnidadOrganizativa $unidad): bool
    {
        return $this->isAuthorized($user);
    }

    /**
     * Determina si el usuario puede desactivar una unidad organizativa.
     */
    public function deactivate(User $user, UnidadOrganizativa $unidad): bool
    {
        return $this->isAuthorized($user);
    }
}