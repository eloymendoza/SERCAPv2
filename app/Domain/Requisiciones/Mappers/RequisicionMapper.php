<?php

namespace App\Domain\Requisiciones\Mappers;

use App\Domain\Requisiciones\DTOs\RequisicionDTO;
use App\Domain\Requisiciones\Models\Requisicion;

/**
 * Capa de transformación de datos para la entidad Requisicion.
 */
class RequisicionMapper
{
    public function __construct(
        private readonly DetalleRequisicionMapper $detalleMapper
    ) {}

    /**
     * Convierte un modelo Requisicion a su respectivo DTO.
     *
     * @param Requisicion $model
     * @return RequisicionDTO
     */
    public function toDTO(Requisicion $model): RequisicionDTO
    {
        $detalleDTO = null;
        if ($model->relationLoaded('detalles') && $model->detalles->isNotEmpty()) {
            $detalleDTO = $this->detalleMapper->toDTO($model->detalles->first());
        }

        return new RequisicionDTO(
            id: $model->id,
            solicitudId: $model->solicitud_id,
            folio: $model->folio,
            tipo: $model->tipo,
            observaciones: $model->observaciones,
            estado: $model->estado,
            detalle: $detalleDTO
        );
    }

    /**
     * Convierte un DTO en un array compatible con la persistencia de Eloquent.
     *
     * @param RequisicionDTO $dto
     * @param int|null $solicitudId
     * @return array<string, mixed>
     */
    public function toPersistenceArray(RequisicionDTO $dto, ?int $solicitudId = null): array
    {
        $data = [
            'folio' => $dto->folio,
            'tipo' => $dto->tipo,
            'observaciones' => $dto->observaciones,
            'estado' => $dto->estado,
        ];

        if (empty($data['estado'])) {
            unset($data['estado']);
        }

        if ($solicitudId !== null) {
            $data['solicitud_id'] = $solicitudId;
        } elseif ($dto->solicitudId !== null) {
            $data['solicitud_id'] = $dto->solicitudId;
        }

        return $data;
    }

    /**
     * Convierte un DTO en un array para actualización.
     *
     * @param RequisicionDTO $dto
     * @return array<string, mixed>
     */
    public function toUpdatePersistenceArray(RequisicionDTO $dto): array
    {
        $data = $this->toPersistenceArray($dto);
        
        if ($dto->estado === null && array_key_exists('estado', $data)) {
            unset($data['estado']);
        }
        
        return $data;
    }

    /**
     * Convierte una colección de modelos a DTOs.
     *
     * @param iterable<Requisicion> $models
     * @return array<int, RequisicionDTO>
     */
    public function toDTOCollection(iterable $models): array
    {
        $dtos = [];
        foreach ($models as $model) {
            $dtos[] = $this->toDTO($model);
        }
        return $dtos;
    }
}
