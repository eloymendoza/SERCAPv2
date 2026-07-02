<?php

namespace App\Domain\Puestos\Mappers;

use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\DTOs\PuestoDTO;

class PuestoMapper
{
    /**
     * Transforma el DTO a un arreglo de base de datos nativo.
     */
    public function toPersistenceArray(PuestoDTO $dto): array
    {
        return [
            'nombre_puesto' => $dto->nombrePuesto,
            'direccion_id' => $dto->direccionId,
            'reporta_a_puesto_id' => $dto->reportaAPuestoId,
            'tipo' => $dto->tipo,
        ];
    }

    /**
     * Transforma un modelo Eloquent a DTO.
     */
    public function toDTO(Puesto $model): PuestoDTO
    {
        return new PuestoDTO(
            nombrePuesto: $model->nombre_puesto,
            direccionId: $model->direccion_id,
            tipo: $model->tipo,
            id: $model->id,
            reportaAPuestoId: $model->reporta_a_puesto_id,
            perfilSgc: $model->relationLoaded('perfilSgc') && $model->perfilSgc ? $model->perfilSgc->toArray() : null,
            urgente: $model->urgente ?? 0
        );
    }
}