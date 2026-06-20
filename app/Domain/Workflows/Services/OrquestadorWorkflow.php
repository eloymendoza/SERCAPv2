<?php

namespace App\Domain\Workflows\Services;

use App\Domain\Autenticacion\Models\User;
use App\Domain\Workflows\Contracts\Workflowable;
use App\Domain\Workflows\Services\WorkflowService;
use App\Domain\Workflows\DTOs\WorkflowInstanceDTO;

/**
 * Motor genérico encargado de la emisión de entidades hacia el Workflow (Django).
 * Orquesta la creación de la instancia si se requieren firmantes, o delega la auto-aprobación al modelo.
 */
class OrquestadorWorkflow
{
    public function __construct(
        private readonly WorkflowService $workflowService
    ) {}

    /**
     * Orquesta la emisión del modelo al workflow o lo auto-aprueba según la resolución de firmantes.
     *
     * @param Workflowable $modelo El modelo que será emitido.
     * @param User $elaborador Usuario que ejecuta la emisión.
     * @param array{requiere_workflow: bool, workflow_id: int, firmantes: array} $resolucionFirmantes Array generado por un FirmantesResolverService.
     */
    public function emitir(Workflowable $modelo, User $elaborador, array $resolucionFirmantes): void
    {
        if (!$resolucionFirmantes['requiere_workflow']) {
            $modelo->autoAprobar();
            return;
        }

        $workflowResponse = $this->workflowService->iniciarInstancia($modelo->getIdentificador(), [
            'Id_personal' => $elaborador->id_personal,
            'workflow_id' => $resolucionFirmantes['workflow_id'],
            'firmantes' => $resolucionFirmantes['firmantes'],
        ]);

        $modelo->aplicarWorkflowInstancia($workflowResponse->idInstancia);
    }

    /**
     * Orquesta la aprobación de un paso del workflow para el modelo dado.
     *
     * @param Workflowable $modelo El modelo que será actualizado.
     * @param User $firmante Usuario que ejecuta la firma.
     * @param string|null $observaciones Observaciones opcionales del firmante.
     * @return \App\Domain\Workflows\DTOs\WorkflowInstanceDTO
     */
    public function aprobarPaso(Workflowable $modelo, User $firmante, ?string $observaciones = null): WorkflowInstanceDTO
    {
        $firmanteActual = $this->workflowService->obtenerFirmanteActual(
            $modelo->getIdentificadorInstancia()
        );

        $workflowResponse = $this->workflowService->aprobarPaso($modelo->getIdentificador(), [
            'id_instancia'  => $modelo->getIdentificadorInstancia(),
            'id_firmante'   => $firmanteActual['id'],
            'Id_personal'   => $firmante->id_personal,
            'observaciones' => $observaciones,
        ]);

        $modelo->sincronizarEstadoWorkflow($workflowResponse->estado);

        return $workflowResponse;
    }

    /**
     * Orquesta el rechazo de un paso del workflow para el modelo dado.
     *
     * @param Workflowable $modelo El modelo que será actualizado.
     * @param User $firmante Usuario que ejecuta el rechazo.
     * @param string|null $observaciones Observaciones opcionales del firmante.
     * @return \App\Domain\Workflows\DTOs\WorkflowInstanceDTO
     */
    public function rechazarPaso(Workflowable $modelo, User $firmante, ?string $observaciones = null): WorkflowInstanceDTO
    {
        $firmanteActual = $this->workflowService->obtenerFirmanteActual(
            $modelo->getIdentificadorInstancia()
        );

        $workflowResponse = $this->workflowService->rechazarPaso($modelo->getIdentificador(), [
            'id_instancia'  => $modelo->getIdentificadorInstancia(),
            'id_firmante'   => $firmanteActual['id'],
            'Id_personal'   => $firmante->id_personal,
            'comentario' => $observaciones,
        ]);

        $modelo->sincronizarEstadoWorkflow($workflowResponse->estado);

        return $workflowResponse;
    }

    /**
     * Orquesta la reemision de un paso del workflow para el modelo dado.
     *
     * @param Workflowable $modelo El modelo que será actualizado.
     * @param User $firmante Usuario que ejecuta la reemision.
     * @param array $resolucionFirmantes La resolución de firmantes en caso de que cambien.
     * @param string|null $observaciones Observaciones opcionales del firmante.
     * @return \App\Domain\Workflows\DTOs\WorkflowInstanceDTO
     */
    public function reiniciar(Workflowable $modelo, User $firmante, array $resolucionFirmantes, ?string $observaciones = null): WorkflowInstanceDTO
    {
        $payload = [
            'id_instancia' => (int) $modelo->getIdentificadorInstancia(),
            'Id_personal'  => (int) $firmante->id_personal,
        ];

        if (!empty($resolucionFirmantes['firmantes'])) {
            $payload['firmantes'] = $resolucionFirmantes['firmantes'];
        }

        if ($observaciones !== null) {
            $payload['observaciones'] = $observaciones;
        }

        $workflowResponse = $this->workflowService->reiniciarInstancia($modelo->getIdentificador(), $payload);

        $modelo->sincronizarEstadoWorkflow($workflowResponse->estado);

        return $workflowResponse;
    }
}