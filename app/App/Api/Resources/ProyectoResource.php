<?php

namespace App\App\Api\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProyectoResource extends JsonResource
{
    /**
     * Transforma el recurso DTO en un arreglo para respuesta HTTP.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'proyecto' => $this->resource->proyecto,
            'descripcion' => $this->resource->descripcion,
            'jefe_proyecto' => $this->resource->jefeProyecto,
            'activo' => $this->resource->activoProyecto,
        ];
    }
}