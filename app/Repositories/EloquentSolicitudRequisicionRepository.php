<?php

namespace App\Repositories;

use App\Contracts\SolicitudRequisicionRepositoryInterface;
use App\DTOs\SolicitudRequisicionDTO;
use App\Mappers\SolicitudRequisicionMapper;
use App\Models\SolicitudRequisicion;
use Illuminate\Support\Facades\DB;

class EloquentSolicitudRequisicionRepository implements SolicitudRequisicionRepositoryInterface
{
    public function __construct(
        private readonly SolicitudRequisicionMapper $mapper,
        private readonly SolicitudRequisicion $model
    ) {}

    public function create(SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        return DB::transaction(function () use ($dto) {
            $data = $this->mapper->toPersistenceArray($dto);
            
            // Removemos estado si viene nulo para no sobreescribir default DB behavior
            if (empty($data['estado'])) {
                unset($data['estado']);
            }
            
            $model = $this->model->create($data);
            return $this->mapper->toDTO($model);
        });
    }

    public function update(int $id, SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        return DB::transaction(function () use ($id, $dto) {
            $model = $this->model->findOrFail($id);
            $data = $this->mapper->toPersistenceArray($dto);
            
            if ($dto->estado === null) {
                unset($data['estado']);
            }
            
            $model->update($data);
            return $this->mapper->toDTO($model);
        });
    }

    public function findById(int $id): SolicitudRequisicionDTO
    {
        $model = $this->model->findOrFail($id);
        return $this->mapper->toDTO($model);
    }

    public function all(): array
    {
        $models = $this->model->all();
        return $this->mapper->toDTOCollection($models);
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $model = $this->model->findOrFail($id);
            $model->delete();
        });
    }
}