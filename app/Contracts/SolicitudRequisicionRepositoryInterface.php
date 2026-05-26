<?php

namespace App\Contracts;

use App\DTOs\SolicitudRequisicionDTO;

interface SolicitudRequisicionRepositoryInterface
{
    public function create(SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO;

    public function update(int $id, SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO;

    public function findById(int $id): SolicitudRequisicionDTO;

    /**
     * @return array<int, SolicitudRequisicionDTO>
     */
    public function all(): array;

    public function delete(int $id): void;
}