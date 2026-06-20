<?php

namespace App\Domain\Requisiciones\Policies;

use App\Domain\Autenticacion\Models\User;
use App\Domain\Workflows\Services\WorkflowService;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\ContextoAutorizacionDTO;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstado;
use App\Domain\Requisiciones\Services\VinculoContextualService;

class SolicitudRequisicionPolicy
{
    public function __construct(
        protected VinculoContextualService $vinculoService,
        protected WorkflowService $workflowService,
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

    /**
     * Determina si el usuario es el firmante activo de la instancia en Django.
     *
     * La solicitud debe estar en estado en_proceso y Django debe confirmar
     * que el turno de firma corresponde al usuario que ejecuta la acción.
     */
    public function aprobar(User $user, SolicitudRequisicion $solicitud): bool
    {
        if ($solicitud->estado !== SolicitudRequisicionEstado::EN_PROCESO) {
            return false;
        }

        $firmanteActual = $this->workflowService->obtenerFirmanteActual(
            $solicitud->id_instancia_workflow
        );

        return isset($firmanteActual['Id_personal']) && (int) $firmanteActual['Id_personal'] === (int) $user->id_personal;
    }

    /**
     * Determina si el usuario puede reiniciar una solicitud de requisición rechazada.
     */
    public function reiniciar(User $user, SolicitudRequisicion $solicitud): bool
    {
        return $solicitud->estado === SolicitudRequisicionEstado::RECHAZADO
            && (int) $user->id_personal === (int) $solicitud->elaborador_id;
    }
}