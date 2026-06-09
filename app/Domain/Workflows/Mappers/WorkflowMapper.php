<?php

namespace App\Domain\Workflows\Mappers;

use App\Domain\Workflows\DTOs\WorkflowInstanceDTO;

class WorkflowMapper
{
    /**
     * Mapea el arreglo JSON devuelto por el API externo (InstanciaSerializer) hacia el DTO interno estricto.
     */
    public function toResponseDTO(array $payload): WorkflowInstanceDTO
    {
        return new WorkflowInstanceDTO(
            idInstancia: $payload['id'] ?? 0,
            workflowId: $payload['workflow'] ?? 0,
            estado: $payload['estado'] ?? 'Desconocido',
            idPersonalEmisor: $payload['Id_personal'] ?? 0,
            firmantes: $payload['firmantes'] ?? [],
            historial: $payload['historial'] ?? [],
            rawResponse: $payload
        );
    }
}
