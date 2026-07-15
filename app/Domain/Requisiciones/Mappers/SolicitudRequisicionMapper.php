<?php

namespace App\Domain\Requisiciones\Mappers;

use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;

/**
 * Capa de transformación de datos para la entidad SolicitudRequisicion.
 */
class SolicitudRequisicionMapper
{
    public function __construct(
        private readonly RequisicionMapper $requisicionMapper
    ) {}
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
            elaboradorId: $model->elaborador_id,
            direccionId: $model->direccion_id,
            gerenciaId: $model->gerencia_id,
            coordinacionId: $model->coordinacion_id,
            requisicionPadreId: $model->requisicion_padre_id,
            observaciones: $model->observaciones,
            estado: $model->estado,
            requisicion: $model->relationLoaded('requisicion') && $model->requisicion
                ? $this->requisicionMapper->toDTO($model->requisicion)
                : null
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
        $data = [
            'folio' => $dto->folio,
            'proyecto_id' => $dto->proyectoId,
            'id_instancia_workflow' => $dto->idInstanciaWorkflow,
            'solicitante_id' => $dto->solicitanteId,
            'elaborador_id' => $dto->elaboradorId,
            'direccion_id' => $dto->direccionId,
            'gerencia_id' => $dto->gerenciaId,
            'coordinacion_id' => $dto->coordinacionId,
            'requisicion_padre_id' => $dto->requisicionPadreId,
            'observaciones' => $dto->observaciones,
            'estado' => $dto->estado,
        ];

        if (empty($data['estado'])) {
            unset($data['estado']);
        }

        return $data;
    }

    /**
     * Convierte un DTO en un array para actualización, omitiendo el estado si es nulo.
     *
     * @param SolicitudRequisicionDTO $dto
     * @return array<string, mixed>
     */
    public function toUpdatePersistenceArray(SolicitudRequisicionDTO $dto): array
    {
        $data = $this->toPersistenceArray($dto);
        
        if ($dto->estado === null && array_key_exists('estado', $data)) {
            unset($data['estado']);
        }

        if ($dto->idInstanciaWorkflow === null && array_key_exists('id_instancia_workflow', $data)) {
            unset($data['id_instancia_workflow']);
        }

        if ($dto->folio === null && array_key_exists('folio', $data)) {
            unset($data['folio']);
        }
        
        return $data;
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