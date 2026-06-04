<?php

namespace App\Domain\Requisiciones\Mappers;

use App\Domain\Requisiciones\DTOs\DetalleRequisicionDTO;
use App\Domain\Requisiciones\Models\DetalleRequisicion;

/**
 * Capa de transformación de datos para la entidad DetalleRequisicion.
 */
class DetalleRequisicionMapper
{
    /**
     * Convierte un modelo DetalleRequisicion a su respectivo DTO.
     *
     * @param DetalleRequisicion $model
     * @return DetalleRequisicionDTO
     */
    public function toDTO(DetalleRequisicion $model): DetalleRequisicionDTO
    {
        return new DetalleRequisicionDTO(
            id: $model->id,
            requisicionId: $model->requisicion_id,
            puestoId: $model->puesto_id,
            cantidadSolicitada: $model->cantidad_solicitada,
            disciplinaId: $model->disciplina_id,
            tipoContrato: $model->tipo_contrato instanceof \BackedEnum ? $model->tipo_contrato->value : (string) $model->tipo_contrato,
            tabuladorSueldo: $model->tabulador_sueldo,
            turnoHoras: $model->turno_horas,
            fechaInicio: $model->fecha_inicio?->format('Y-m-d'),
            fechaTermino: $model->fecha_termino?->format('Y-m-d'),
            fechaLimiteRequerimiento: $model->fecha_limite_requerimiento?->format('Y-m-d'),
            empleadosPropuestos: is_string($model->empleados_propuestos) 
                ? explode(',', $model->empleados_propuestos) 
                : $model->empleados_propuestos
        );
    }

    /**
     * Convierte un DTO en un array compatible con la persistencia de Eloquent.
     *
     * @param DetalleRequisicionDTO $dto
     * @param int|null $requisicionId
     * @return array<string, mixed>
     */
    public function toPersistenceArray(DetalleRequisicionDTO $dto, ?int $requisicionId = null): array
    {
        $data = [
            'puesto_id' => $dto->puestoId,
            'cantidad_solicitada' => $dto->cantidadSolicitada,
            'disciplina_id' => $dto->disciplinaId,
            'tipo_contrato' => $dto->tipoContrato,
            'tabulador_sueldo' => $dto->tabuladorSueldo,
            'turno_horas' => $dto->turnoHoras,
            'fecha_inicio' => $dto->fechaInicio,
            'fecha_termino' => $dto->fechaTermino,
            'fecha_limite_requerimiento' => $dto->fechaLimiteRequerimiento,
            'empleados_propuestos' => is_array($dto->empleadosPropuestos) 
                ? implode(',', $dto->empleadosPropuestos) 
                : $dto->empleadosPropuestos,
        ];

        if ($requisicionId !== null) {
            $data['requisicion_id'] = $requisicionId;
        } elseif ($dto->requisicionId !== null) {
            $data['requisicion_id'] = $dto->requisicionId;
        }

        return $data;
    }

    /**
     * Convierte un DTO en un array para actualización.
     *
     * @param DetalleRequisicionDTO $dto
     * @return array<string, mixed>
     */
    public function toUpdatePersistenceArray(DetalleRequisicionDTO $dto): array
    {
        return $this->toPersistenceArray($dto);
    }

    /**
     * Convierte una colección de modelos a DTOs.
     *
     * @param iterable<DetalleRequisicion> $models
     * @return array<int, DetalleRequisicionDTO>
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
