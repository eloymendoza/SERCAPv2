<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\SolicitudRequisicion;
use App\DTOs\SolicitudRequisicionDTO;
use App\Mappers\SolicitudRequisicionMapper;
use App\Contracts\SolicitudRequisicionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
            
            $model = $this->model->create($data);
            return $this->mapper->toDTO($model);
        });
    }

    public function update(int $id, SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        return DB::transaction(function () use ($id, $dto) {
            $model = $this->model->findOrFail($id);
            $data = $this->mapper->toUpdatePersistenceArray($dto);
            
            $model->update($data);
            return $this->mapper->toDTO($model);
        });
    }

    public function findById(int $id): SolicitudRequisicionDTO
    {
        $model = $this->model->findOrFail($id);
        return $this->mapper->toDTO($model);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $paginator = $this->model->paginate($perPage);
        
        $paginator->getCollection()->transform(function ($model) {
            return $this->mapper->toDTO($model);
        });

        return $paginator;
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $model = $this->model->findOrFail($id);
            $model->delete();
        });
    }
}