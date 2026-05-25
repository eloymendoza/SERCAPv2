<?php

namespace App\Mappers;

use App\DTOs\SolicitudRequisicionDTO;
use App\Models\SolicitudRequisicion;

/**
 * Capa de transformación de datos para la entidad SolicitudRequisicion.
 */
class SolicitudRequisicionMapper
{
    /**
     * Convierte un modelo SolicitudRequisicion a su respectivo DTO.
     *
     * @param SolicitudRequisicion $model
     * @return SolicitudRequisicionDTO
     */
    public function toDTO(SolicitudRequisicion $model): SolicitudRequisicionDTO
    {
        return new SolicitudRequisicionDTO(
            id: $model->id,
            folio: $model->folio,
            proyectoId: $model->proyecto_id,
            idInstanciaWorkflow: $model->id_instancia_workflow,
            solicitanteId: $model->solicitante_id,
            direccionId: $model->direccion_id,
            gerenciaId: $model->gerencia_id,
            coordinacionId: $model->coordinacion_id,
            observaciones: $model->observaciones,
            estado: $model->estado
        );
    }

    /**
     * Convierte un DTO en un array compatible con la persistencia de Eloquent.
     *
     * @param SolicitudRequisicionDTO $dto
     * @return array<string, mixed>
     */
    public function toPersistenceArray(SolicitudRequisicionDTO $dto): array
    {
        return [
            'folio' => $dto->folio,
            'proyecto_id' => $dto->proyectoId,
            'id_instancia_workflow' => $dto->idInstanciaWorkflow,
            'solicitante_id' => $dto->solicitanteId,
            'direccion_id' => $dto->direccionId,
            'gerencia_id' => $dto->gerenciaId,
            'coordinacion_id' => $dto->coordinacionId,
            'observaciones' => $dto->observaciones,
            'estado' => $dto->estado,
        ];
    }

    /**
     * Convierte un DTO en un array compatible con respuestas HTTP.
     *
     * @param SolicitudRequisicionDTO $dto
     * @return array<string, mixed>
     */
    public function toResponseArray(SolicitudRequisicionDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'folio' => $dto->folio,
            'proyecto_id' => $dto->proyectoId,
            'id_instancia_workflow' => $dto->idInstanciaWorkflow,
            'solicitante_id' => $dto->solicitanteId,
            'direccion_id' => $dto->direccionId,
            'gerencia_id' => $dto->gerenciaId,
            'coordinacion_id' => $dto->coordinacionId,
            'observaciones' => $dto->observaciones,
            'estado' => [
                'value' => $dto->estado->value,
                'label' => $dto->estado->label(),
                'color' => $dto->estado->color(),
            ],
        ];
    }

    /**
     * Convierte una colección de modelos SolicitudRequisicion a DTOs.
     *
     * @param iterable<SolicitudRequisicion> $models
     * @return array<int, SolicitudRequisicionDTO>
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
