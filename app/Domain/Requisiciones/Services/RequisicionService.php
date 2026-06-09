<?php

namespace App\Domain\Requisiciones\Services;

use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\DTOs\RequisicionDTO;
use App\Domain\Requisiciones\Mappers\RequisicionMapper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RequisicionService
{
    use HandlesProcess;

    public function __construct(
        private readonly RequisicionMapper $mapper,
        private readonly DetalleRequisicionService $detalleService
    ) {}

    protected function getLogChannel(): string
    {
        return 'requisicion';
    }

    /**
     * Procesa la creación de una requisición base y sus detalles.
     */
    public function create(RequisicionDTO $dto, int $solicitudId): RequisicionDTO
    {
        $this->logger()->info("Procesando creación de requisición", [
            'solicitud_id' => $solicitudId
        ]);

        return $this->handle(function () use ($dto, $solicitudId) {
            $data = $this->mapper->toPersistenceArray($dto, $solicitudId);
            $model = Requisicion::create($data);
            
            if ($dto->detalle) {
                $this->detalleService->create($dto->detalle, $model->id);
            }

            $model->load('detalles');
            return $this->mapper->toDTO($model);
        }, 'RequisicionService@create');
    }

    public function update(int $id, RequisicionDTO $dto): RequisicionDTO
        {
        $this->logger()->info("Iniciando actualización de requisición.", [
            'id' => $id
        ]);

        return $this->handle(function () use ($id, $dto) {
            $updatedDto = DB::transaction(function () use ($id, $dto) {
                $model = Requisicion::findOrFail($id);
                $data = $this->mapper->toUpdatePersistenceArray($dto);
                
                $model->update($data);
                return $this->mapper->toDTO($model);
            });

            $this->logger()->info("Requisición actualizada.", [
                'id' => $id
            ]);

            return $updatedDto;
        }, 'RequisicionService@update');
    }

    public function find(int $id): RequisicionDTO
    {
        $this->logger()->info("Consultando detalle de requisición.", [
            'id' => $id
        ]);

        return $this->handle(function () use ($id) {
            $model = Requisicion::findOrFail($id);
            return $this->mapper->toDTO($model);
        }, 'RequisicionService@find');
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $this->logger()->info("Consultando colección paginada de requisiciones");

        return $this->handle(function () use ($perPage) {
            $paginator = Requisicion::paginate($perPage);
            
            $paginator->getCollection()->transform(function ($model) {
                return $this->mapper->toDTO($model);
            });

            return $paginator;
        }, 'RequisicionService@paginate');
    }

    public function delete(int $id): void
    {
        $this->logger()->info("Iniciando eliminación de requisición.", [
            'id' => $id
        ]);

        $this->handle(function () use ($id) {
            DB::transaction(function () use ($id) {
                $model = Requisicion::findOrFail($id);
                
                foreach ($model->detalles as $detalle) {
                    $this->detalleService->delete($detalle->id);
                }

                $model->delete();
            });

            $this->logger()->info("Requisición eliminada.", [
                'id' => $id
            ]);
        }, 'RequisicionService@delete');
    }
}