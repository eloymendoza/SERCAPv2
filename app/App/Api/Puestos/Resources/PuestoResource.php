<?php

namespace App\App\Api\Puestos\Resources;

use Illuminate\Http\Request;
use App\Domain\Puestos\DTOs\PuestoDTO;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PuestoDTO $resource
 */
class PuestoResource extends JsonResource
{
    /**
     * Transforma el DTO a un arreglo de respuesta serializable.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'nombre_puesto' => $this->resource->nombrePuesto,
            'direccion_id' => $this->resource->direccionId,
            'reporta_a_puesto_id' => $this->resource->reportaAPuestoId,
            'tipo' => $this->resource->tipo,
            'urgente' => $this->resource->urgente,
            'perfil_sgc' => $this->resource->perfilSgc,
            'estado' => $this->resource->estado,
            'direccion' => $this->resource->direccion,
        ];
    }
}