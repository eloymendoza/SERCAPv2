<?php

namespace App\Domain\Puestos\Mappers;

use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\DTOs\PuestoDTO;
use App\Domain\Puestos\Services\PerfilSgcService;

class PuestoMapper
{
    public function __construct(
        private readonly PerfilSgcService $sgcService
    ) {}

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
        $perfilSgc = null;

        if ($model->relationLoaded('perfilSgc') && $model->perfilSgc) {
            $perfilSgc = $model->perfilSgc->toArray();

            if (isset($perfilSgc['id_documento'])) {
                $perfilesDict = $this->sgcService->getPerfilesActivosDiccionario();
                $sgcDoc = $perfilesDict->get($perfilSgc['id_documento']);
                
                if ($sgcDoc) {
                    $perfilSgc['identificacion'] = $sgcDoc->identificacion;
                    $perfilSgc['titulo'] = $sgcDoc->titulo;
                    $perfilSgc['revision'] = $sgcDoc->revision;
                    $perfilSgc['estado_sgc'] = $sgcDoc->estado;
                }
            }
        }

        return new PuestoDTO(
            nombrePuesto: $model->nombre_puesto,
            direccionId: $model->direccion_id,
            tipo: $model->tipo,
            id: $model->id,
            reportaAPuestoId: $model->reporta_a_puesto_id,
            perfilSgc: $perfilSgc,
            urgente: $model->urgente ?? 0,
            estado: $model->estado,
            direccion: $model->relationLoaded('direccion') && $model->direccion ? $model->direccion->only(['id', 'nombre', 'abreviatura', 'nivel']) : null
        );
    }
}