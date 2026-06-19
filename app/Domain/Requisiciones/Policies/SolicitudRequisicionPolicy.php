<?php

namespace App\Domain\Requisiciones\Policies;

use App\Domain\Autenticacion\Models\User;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\ContextoAutorizacionDTO;

use App\Domain\Requisiciones\Services\VinculoContextualService;

class SolicitudRequisicionPolicy
{
    public function __construct(
        protected VinculoContextualService $vinculoService
    ) {}

    /**
     * Determina si el usuario puede crear una solicitud de requisición.
     *
     * EAP opera sin restricción contextual. Los demás roles deben tener vínculo
     * directo con la dirección o proyecto especificados en la solicitud.
     */
    public function create(User $user, ?int $direccionId = null, ?int $proyectoId = null): bool
    {
        if ($user->can('EAP')) {
            return true;
        }

        $contexto = new ContextoAutorizacionDTO($user, $direccionId, $proyectoId);
        return $this->vinculoService->tieneVinculoContextual($contexto);
    }

    /**
     * Determina si el usuario puede modificar una solicitud de requisición.
     *
     * Solo el elaborador original puede editar; el solicitante no tiene este derecho.
     */
    public function update(User $user, SolicitudRequisicion $solicitud): bool
    {
        return $user->id_personal === $solicitud->elaborador_id;
    }
}