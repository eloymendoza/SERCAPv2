<?php

namespace App\App\Api\Requisiciones\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudRequisicionResource extends JsonResource
{
    /**
     * Transforma el DTO/Modelo en una respuesta JSON estructurada.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'proyecto_id' => $this->proyectoId,
            'id_instancia_workflow' => $this->idInstanciaWorkflow,
            'solicitante_id' => $this->solicitanteId,
            'elaborador_id' => $this->elaboradorId,
            'direccion_id' => $this->direccionId,
            'gerencia_id' => $this->gerenciaId,
            'coordinacion_id' => $this->coordinacionId,
            'observaciones' => $this->observaciones,
            'estado' => $this->estado ? [
                'value' => $this->estado->value,
                'label' => $this->estado->label(),
                'color' => $this->estado->color(),
            ] : null,
        ];
    }
}
