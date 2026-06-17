<?php

namespace App\Domain\Requisiciones\Policies;

use App\Domain\Autenticacion\Models\User;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;

class SolicitudRequisicionPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('EAP') 
            || $user->isDirector() 
            || $user->isGerenteProyecto() 
            || $user->isJefeProyecto();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SolicitudRequisicion $solicitud): bool
    {
        return $this->create($user)
            && ($user->id_personal === $solicitud->elaborador_id
                || $user->id_personal === $solicitud->solicitante_id);
    }
}