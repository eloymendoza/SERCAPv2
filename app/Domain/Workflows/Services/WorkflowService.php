<?php

namespace App\Domain\Workflows\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\Log;
use App\Infrastructure\Clients\WorkflowClient;
use App\Domain\Workflows\DTOs\WorkflowInstanceDTO;
use App\Domain\Workflows\Mappers\WorkflowMapper;

class WorkflowService
{
    use HandlesProcess;

    public function __construct(
        private readonly WorkflowClient $client,
        private readonly WorkflowMapper $mapper,
        private readonly LogContext $logContext
    ) {}

    protected function getLogChannel(): string
    {
        return 'workflow';
    }

    public function iniciarInstancia(int $id, array $payload): WorkflowInstanceDTO
    {
        Log::channel($this->resolveChannel())->info("Iniciando instancia workflow para ID: {$id}", ['payload' => $payload]);

        return $this->handle(function () use ($id, $payload) {
            $response = $this->client->post('/instancias/iniciar/', $payload);
            return $this->mapper->toResponseDTO($response);
        }, 'WorkflowService@iniciarInstancia');
    }

    public function aprobarPaso(int $id, array $payload): WorkflowInstanceDTO
    {
        Log::channel($this->resolveChannel())->info("Aprobando paso workflow para ID: {$id}", ['payload' => $payload]);

        return $this->handle(function () use ($id, $payload) {
            $response = $this->client->post('/firmantes/aprobar/', $payload);
            return $this->mapper->toResponseDTO($response);
        }, 'WorkflowService@aprobarPaso');
    }

    public function rechazarPaso(int $id, array $payload): WorkflowInstanceDTO
    {
        Log::channel($this->resolveChannel())->info("Rechazando paso workflow para ID: {$id}", ['payload' => $payload]);

        return $this->handle(function () use ($id, $payload) {
            $response = $this->client->post('/firmantes/rechazar/', $payload);
            return $this->mapper->toResponseDTO($response);
        }, 'WorkflowService@rechazarPaso');
    }

    public function cancelarInstancia(int $id, array $payload): WorkflowInstanceDTO
    {
        Log::channel($this->resolveChannel())->info("Cancelando instancia workflow para ID: {$id}", ['payload' => $payload]);

        return $this->handle(function () use ($id, $payload) {
            $response = $this->client->post('/instancias/cancelar/', $payload);
            return $this->mapper->toResponseDTO($response);
        }, 'WorkflowService@cancelInstancia');
    }

    public function reiniciarInstancia(int $id, array $payload): WorkflowInstanceDTO
    {
        Log::channel($this->resolveChannel())->info("Reiniciando instancia workflow para ID: {$id}", ['payload' => $payload]);

        return $this->handle(function () use ($id, $payload) {
            $response = $this->client->post('/instancias/reiniciar/', $payload);
            return $this->mapper->toResponseDTO($response);
        }, 'WorkflowService@reiniciarInstancia');
    }
}