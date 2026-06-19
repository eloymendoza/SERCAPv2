<?php

namespace App\Domain\Workflows\Services;

use App\Traits\HandlesProcess;
use App\Infrastructure\Clients\WorkflowClient;
use App\Domain\Workflows\Mappers\WorkflowMapper;
use App\Domain\Workflows\DTOs\WorkflowInstanceDTO;

class WorkflowService
{
    use HandlesProcess;

    public function __construct(
        private readonly WorkflowClient $client,
        private readonly WorkflowMapper $mapper
    ) {}

    protected function getLogChannel(): string
    {
        return 'workflow';
    }

    public function iniciarInstancia(int $id, array $payload): WorkflowInstanceDTO
    {
        $this->logger()->info("Iniciando instancia workflow para ID: {$id}", ['payload' => $payload]);

        return $this->handle(function () use ($id, $payload) {
            $response = $this->client->post('/instancias/iniciar/', $payload);
            return $this->mapper->toResponseDTO($response);
        }, 'WorkflowService@iniciarInstancia');
    }

    /**
     * Consulta el firmante activo de una instancia de workflow en Django.
     *
     * @return array{Id_personal: int, orden: int, ...}
     */
    public function obtenerFirmanteActual(int $idInstancia): array
    {
        $this->logger()->info("Consultando firmante actual para instancia: {$idInstancia}");

        return $this->handle(function () use ($idInstancia) {
            return $this->client->post('/instancias/firmante_actual/', [
                'id_instancia' => $idInstancia,
            ]);
        }, 'WorkflowService@obtenerFirmanteActual');
    }

    public function aprobarPaso(int $id, array $payload): WorkflowInstanceDTO
    {
        $this->logger()->info("Aprobando paso workflow para ID: {$id}", ['payload' => $payload]);

        return $this->handle(function () use ($id, $payload) {
            $response = $this->client->post('/firmantes/aprobar/', $payload);
            return $this->mapper->toResponseDTO($response);
        }, 'WorkflowService@aprobarPaso');
    }

    public function rechazarPaso(int $id, array $payload): WorkflowInstanceDTO
    {
        $this->logger()->info("Rechazando paso workflow para ID: {$id}", ['payload' => $payload]);

        return $this->handle(function () use ($id, $payload) {
            $response = $this->client->post('/firmantes/rechazar/', $payload);
            return $this->mapper->toResponseDTO($response);
        }, 'WorkflowService@rechazarPaso');
    }

    public function cancelarInstancia(int $id, array $payload): WorkflowInstanceDTO
    {
        $this->logger()->info("Cancelando instancia workflow para ID: {$id}", ['payload' => $payload]);

        return $this->handle(function () use ($id, $payload) {
            $response = $this->client->post('/instancias/cancelar/', $payload);
            return $this->mapper->toResponseDTO($response);
        }, 'WorkflowService@cancelInstancia');
    }

    public function reiniciarInstancia(int $id, array $payload): WorkflowInstanceDTO
    {
        $this->logger()->info("Reiniciando instancia workflow para ID: {$id}", ['payload' => $payload]);

        return $this->handle(function () use ($id, $payload) {
            $response = $this->client->post('/instancias/reiniciar/', $payload);
            return $this->mapper->toResponseDTO($response);
        }, 'WorkflowService@reiniciarInstancia');
    }
}