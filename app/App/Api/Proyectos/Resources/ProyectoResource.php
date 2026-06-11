<?php

namespace App\App\Api\Proyectos\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProyectoResource extends JsonResource
{
    /**
     * Transforma el modelo en un arreglo para respuesta HTTP.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->idProyecto,
            'proyecto' => $this->proyecto,
            'descripcion' => $this->descripcion,
            'jefe_proyecto' => $this->jefeProyecto,
            'activo' => $this->activoProyecto,
        ];
    }
}