<?php

namespace App\Domain\Requisiciones\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domain\Requisiciones\Models\DetalleRequisicion;
use App\Domain\Requisiciones\DTOs\DetalleRequisicionDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domain\Requisiciones\Mappers\DetalleRequisicionMapper;

class DetalleRequisicionService
{
    use HandlesProcess;

    public function __construct(
        private readonly DetalleRequisicionMapper $mapper,
        private readonly LogContext $logContext
    ) {}

    protected function getLogChannel(): string
    {
        return 'requisicion';
    }

    /**
     * Registra un nuevo detalle vinculado a una requisición.
     */
    public function create(DetalleRequisicionDTO $dto, int $requisicionId): DetalleRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Registrando detalle de requisición.", [
            'requisicion_id' => $requisicionId,
            'puesto_id' => $dto->puestoId
        ]);

        return $this->handle(function () use ($dto, $requisicionId) {
            $data = $this->mapper->toPersistenceArray($dto, $requisicionId);
            $model = DetalleRequisicion::create($data);
            return $this->mapper->toDTO($model);
        }, 'DetalleRequisicionService@create');
    }

    public function update(int $id, DetalleRequisicionDTO $dto): DetalleRequisicionDTO
        {
        Log::channel($this->logContext->channel())->info("Iniciando actualización de detalle.", [
            'id' => $id
        ]);

        return $this->handle(function () use ($id, $dto) {
            $updatedDto = DB::transaction(function () use ($id, $dto) {
                $model = DetalleRequisicion::findOrFail($id);
                $data = $this->mapper->toUpdatePersistenceArray($dto);
                
                $model->update($data);
                return $this->mapper->toDTO($model);
            });

            Log::channel($this->logContext->channel())->info("Detalle actualizado.", [
                'id' => $id
            ]);

            return $updatedDto;
        }, 'DetalleRequisicionService@update');
    }

    public function find(int $id): DetalleRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Consultando detalle.", [
            'id' => $id
        ]);

        return $this->handle(function () use ($id) {
            $model = DetalleRequisicion::findOrFail($id);
            return $this->mapper->toDTO($model);
        }, 'DetalleRequisicionService@find');
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        Log::channel($this->logContext->channel())->info("Consultando colección paginada de detalles");

        return $this->handle(function () use ($perPage) {
            $paginator = DetalleRequisicion::paginate($perPage);
            
            $paginator->getCollection()->transform(function ($model) {
                return $this->mapper->toDTO($model);
            });

            return $paginator;
        }, 'DetalleRequisicionService@paginate');
    }

    public function delete(int $id): void
    {
        Log::channel($this->logContext->channel())->info("Iniciando eliminación de detalle.", [
            'id' => $id
        ]);

        $this->handle(function () use ($id) {
            DB::transaction(function () use ($id) {
                $model = DetalleRequisicion::findOrFail($id);
                $model->delete();
            });

            Log::channel($this->logContext->channel())->info("Detalle eliminado.", [
                'id' => $id
            ]);
        }, 'DetalleRequisicionService@delete');
    }
}