<?php

namespace App\Domain\Requisiciones\Services;

use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domain\Requisiciones\Mappers\SolicitudRequisicionMapper;

class SolicitudRequisicionService
{
    use HandlesProcess;

    public function __construct(
        private readonly SolicitudRequisicionMapper $mapper,
        private readonly RequisicionService $requisicionService,
    ) {}

    protected function getLogChannel(): string
    {
        return 'requisicion';
    }

    public function create(SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        $this->logger()->info("Iniciando creación de solicitud.", [
            'folio' => $dto->folio
        ]);

        return $this->handle(function () use ($dto) {
            $createdDto = DB::transaction(function () use ($dto) {
                $data = $this->mapper->toPersistenceArray($dto);
                $model = SolicitudRequisicion::create($data);
                
                if ($dto->requisicion) {
                    $this->requisicionService->create($dto->requisicion, $model->id);
                }

                $model->load('requisicion.detalles');
                return $this->mapper->toDTO($model);
            });

            $this->logger()->info("Solicitud creada.", [
                'id' => $createdDto->id
            ]);

            return $createdDto;
        }, 'SolicitudRequisicionService@create');
    }

    public function update(int $id, SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        $this->logger()->info("Iniciando actualización de solicitud.", [
            'id' => $id
        ]);

        return $this->handle(function () use ($id, $dto) {
            $updatedDto = DB::transaction(function () use ($id, $dto) {
                $model = SolicitudRequisicion::findOrFail($id);
                $data = $this->mapper->toUpdatePersistenceArray($dto);
                
                $model->update($data);
                return $this->mapper->toDTO($model);
            });

            $this->logger()->info("Solicitud actualizada.", [
                'id' => $id
            ]);

            return $updatedDto;
        }, 'SolicitudRequisicionService@update');
    }

    public function find(int $id): SolicitudRequisicionDTO
    {
        $this->logger()->info("Consultando detalle de solicitud.", [
            'id' => $id
        ]);

        return $this->handle(function () use ($id) {
            $model = SolicitudRequisicion::findOrFail($id);
            return $this->mapper->toDTO($model);
        }, 'SolicitudRequisicionService@find');
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $this->logger()->info("Consultando colección paginada de solicitudes");

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
        $this->logger()->info("Iniciando eliminación de solicitud.", [
            'id' => $id
        ]);

        $this->handle(function () use ($id) {
            DB::transaction(function () use ($id) {
                $model = SolicitudRequisicion::findOrFail($id);
                
                if ($model->requisicion) {
                    $this->requisicionService->delete($model->requisicion->id);
                }
                
                $model->delete();
            });

            $this->logger()->info("Solicitud eliminada.", [
                'id' => $id
            ]);
        }, 'SolicitudRequisicionService@delete');
    }
}