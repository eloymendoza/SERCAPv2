<?php

namespace App\Domain\Requisiciones\Services;

use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use App\Domain\Requisiciones\Models\DetalleRequisicion;
use App\Domain\Requisiciones\DTOs\DetalleRequisicionDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domain\Requisiciones\Mappers\DetalleRequisicionMapper;

class DetalleRequisicionService
{
    use HandlesProcess;

    public function __construct(
        private readonly DetalleRequisicionMapper $mapper
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
        $this->logger()->info("Procesando creación de detalle de requisición", [
            'requisicion_id' => $requisicionId,
            'puesto_id' => $dto->puestoId
        ]);

        return $this->handle(function () use ($dto, $requisicionId) {
            $data = $this->mapper->toPersistenceArray($dto, $requisicionId);
            $model = DetalleRequisicion::create($data);

            if ($dto->puestoId === null && $dto->propuestaNombre !== null) {
                \App\Domain\Requisiciones\Models\PropuestaPuesto::create([
                    'detalle_requisicion_id' => $model->id,
                    'nombre_puesto' => $dto->propuestaNombre,
                    'reporta_a_puesto_id' => $dto->propuestaReportaA,
                    'tipo' => $dto->propuestaTipo,
                ]);
            }

            return $this->mapper->toDTO($model);
        }, 'DetalleRequisicionService@create');
    }

    public function update(int $id, DetalleRequisicionDTO $dto): DetalleRequisicionDTO
        {
        $this->logger()->info("Iniciando actualización de detalle.", [
            'id' => $id
        ]);

        return $this->handle(function () use ($id, $dto) {
            $updatedDto = DB::transaction(function () use ($id, $dto) {
                $model = DetalleRequisicion::findOrFail($id);
                $data = $this->mapper->toUpdatePersistenceArray($dto);
                
                $model->update($data);

                if ($dto->puestoId === null && $dto->propuestaNombre !== null) {
                    \App\Domain\Requisiciones\Models\PropuestaPuesto::updateOrCreate(
                        ['detalle_requisicion_id' => $model->id],
                        [
                            'nombre_puesto' => $dto->propuestaNombre,
                            'reporta_a_puesto_id' => $dto->propuestaReportaA,
                            'tipo' => $dto->propuestaTipo,
                        ]
                    );
                } elseif ($dto->puestoId !== null) {
                    \App\Domain\Requisiciones\Models\PropuestaPuesto::where('detalle_requisicion_id', $model->id)->delete();
                }

                return $this->mapper->toDTO($model);
            });

            $this->logger()->info("Detalle actualizado.", [
                'id' => $id
            ]);

            return $updatedDto;
        }, 'DetalleRequisicionService@update');
    }

    public function find(int $id): DetalleRequisicionDTO
    {
        $this->logger()->info("Consultando detalle.", [
            'id' => $id
        ]);

        return $this->handle(function () use ($id) {
            $model = DetalleRequisicion::findOrFail($id);
            return $this->mapper->toDTO($model);
        }, 'DetalleRequisicionService@find');
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $this->logger()->info("Consultando colección paginada de detalles");

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
        $this->logger()->info("Iniciando eliminación de detalle.", [
            'id' => $id
        ]);

        $this->handle(function () use ($id) {
            DB::transaction(function () use ($id) {
                $model = DetalleRequisicion::findOrFail($id);
                $model->delete();
            });

            $this->logger()->info("Detalle eliminado.", [
                'id' => $id
            ]);
        }, 'DetalleRequisicionService@delete');
    }
}