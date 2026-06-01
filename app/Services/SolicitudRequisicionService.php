<?php

namespace App\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SolicitudRequisicion;
use App\DTOs\SolicitudRequisicionDTO;
use App\Mappers\SolicitudRequisicionMapper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SolicitudRequisicionService
{
    use HandlesProcess;

    public function __construct(
        private readonly SolicitudRequisicionMapper $mapper,
        private readonly LogContext $logContext
    ) {}

    protected function getLogChannel(): string
    {
        return 'requisicion';
    }

    public function create(SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Iniciando creación de solicitud.", [
            'folio' => $dto->folio
        ]);

        return $this->handle(function () use ($dto) {
            $createdDto = DB::transaction(function () use ($dto) {
                $data = $this->mapper->toPersistenceArray($dto);
                $model = SolicitudRequisicion::create($data);
                
                return $this->mapper->toDTO($model);
            });

            Log::channel($this->logContext->channel())->info("Solicitud creada.", [
                'id' => $createdDto->id
            ]);

            return $createdDto;
        }, 'SolicitudRequisicionService@create');
    }

    public function update(int $id, SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Iniciando actualización de solicitud.", [
            'id' => $id
        ]);

        return $this->handle(function () use ($id, $dto) {
            $updatedDto = DB::transaction(function () use ($id, $dto) {
                $model = SolicitudRequisicion::findOrFail($id);
                $data = $this->mapper->toUpdatePersistenceArray($dto);
                
                $model->update($data);
                return $this->mapper->toDTO($model);
            });

            Log::channel($this->logContext->channel())->info("Solicitud actualizada.", [
                'id' => $id
            ]);

            return $updatedDto;
        }, 'SolicitudRequisicionService@update');
    }

    public function find(int $id): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Consultando detalle de solicitud.", [
            'id' => $id
        ]);

        return $this->handle(function () use ($id) {
            $model = SolicitudRequisicion::findOrFail($id);
            return $this->mapper->toDTO($model);
        }, 'SolicitudRequisicionService@find');
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        Log::channel($this->logContext->channel())->info("Consultando colección paginada de solicitudes");

        return $this->handle(function () use ($perPage) {
            $paginator = SolicitudRequisicion::paginate($perPage);
            
            $paginator->getCollection()->transform(function ($model) {
                return $this->mapper->toDTO($model);
            });

            return $paginator;
        }, 'SolicitudRequisicionService@paginate');
    }

    public function delete(int $id): void
    {
        Log::channel($this->logContext->channel())->info("Iniciando eliminación de solicitud.", [
            'id' => $id
        ]);

        $this->handle(function () use ($id) {
            DB::transaction(function () use ($id) {
                $model = SolicitudRequisicion::findOrFail($id);
                $model->delete();
            });

            Log::channel($this->logContext->channel())->info("Solicitud eliminada.", [
                'id' => $id
            ]);
        }, 'SolicitudRequisicionService@delete');
    }
}