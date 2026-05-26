<?php

namespace App\Services;

use App\Contracts\SolicitudRequisicionRepositoryInterface;
use App\DTOs\SolicitudRequisicionDTO;
use App\Enums\SolicitudRequisicionEstado;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\Log;
use App\Logging\LogContext;

class SolicitudRequisicionService
{
    use HandlesProcess;

    public function __construct(
        private readonly SolicitudRequisicionRepositoryInterface $repository,
        private readonly LogContext $logContext
    ) {}

    protected function getLogChannel(): string
    {
        return 'requisicion';
    }

    public function create(SolicitudRequisicionDTO $dto, ?string $accion = null): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Iniciando creación de solicitud: {$dto->folio}");

        return $this->handle(function () use ($dto, $accion) {
            if ($accion === 'emitir') {
                $dto = $dto->withEstado(SolicitudRequisicionEstado::EN_PROCESO);
            }

            $createdDto = $this->repository->create($dto);
            Log::channel($this->logContext->channel())->info("Solicitud creada con ID: {$createdDto->id}");

            return $createdDto;
        }, 'SolicitudRequisicionService@create');
    }

    public function update(int $id, SolicitudRequisicionDTO $dto, ?string $accion = null): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Iniciando actualización de solicitud ID: {$id}");

        return $this->handle(function () use ($id, $dto, $accion) {
            if ($accion === 'emitir') {
                $dto = $dto->withEstado(SolicitudRequisicionEstado::EN_PROCESO);
            }

            $updatedDto = $this->repository->update($id, $dto);
            Log::channel($this->logContext->channel())->info("Solicitud ID {$id} actualizada con éxito.");

            return $updatedDto;
        }, 'SolicitudRequisicionService@update');
    }

    public function find(int $id): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Buscando solicitud ID: {$id}");

        return $this->handle(function () use ($id) {
            return $this->repository->findById($id);
        }, 'SolicitudRequisicionService@find');
    }

    public function list(): array
    {
        Log::channel($this->logContext->channel())->info("Consultando colección de solicitudes");

        return $this->handle(function () {
            return $this->repository->all();
        }, 'SolicitudRequisicionService@list');
    }

    public function delete(int $id): void
    {
        Log::channel($this->logContext->channel())->info("Eliminando solicitud ID: {$id}");

        $this->handle(function () use ($id) {
            $this->repository->delete($id);
            Log::channel($this->logContext->channel())->info("Solicitud ID {$id} eliminada.");
        }, 'SolicitudRequisicionService@delete');
    }
}