<?php

namespace App\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\Log;
use App\DTOs\SolicitudRequisicionDTO;
use App\Contracts\SolicitudRequisicionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function create(SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Iniciando creación de solicitud: {$dto->folio}");

        return $this->handle(function () use ($dto) {
            $createdDto = $this->repository->create($dto);
            Log::channel($this->logContext->channel())->info("Solicitud creada con ID: {$createdDto->id}");

            return $createdDto;
        }, 'SolicitudRequisicionService@create');
    }

    public function update(int $id, SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Iniciando actualización de solicitud ID: {$id}");

        return $this->handle(function () use ($id, $dto) {
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

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        Log::channel($this->logContext->channel())->info("Consultando colección paginada de solicitudes");

        return $this->handle(function () use ($perPage) {
            return $this->repository->paginate($perPage);
        }, 'SolicitudRequisicionService@paginate');
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