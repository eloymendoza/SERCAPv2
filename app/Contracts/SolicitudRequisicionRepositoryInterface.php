<?php

namespace App\Contracts;

use App\DTOs\SolicitudRequisicionDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SolicitudRequisicionRepositoryInterface
{
    public function create(SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO;

    public function update(int $id, SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO;

    public function findById(int $id): SolicitudRequisicionDTO;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function delete(int $id): void;
}