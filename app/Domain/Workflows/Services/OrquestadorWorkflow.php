<?php

namespace App\Domain\Workflows\Services;

use App\Domain\Autenticacion\Models\User;
use App\Domain\Workflows\Contracts\Workflowable;
use App\Domain\Workflows\Services\WorkflowService;

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
     * Emite el modelo al workflow o lo auto-aprueba según la resolución de firmantes.
     *
     * @param Workflowable $modelo El modelo que será emitido.
     * @param User $elaborador Usuario que ejecuta la emisión.
     * @param array{requiere_workflow: bool, workflow_id: int, firmantes: array} $resolucionFirmantes Array generado por un FirmantesResolver.
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
}