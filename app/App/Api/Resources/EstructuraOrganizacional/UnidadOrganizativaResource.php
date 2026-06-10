<?php

namespace App\App\Api\Resources\EstructuraOrganizacional;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnidadOrganizativaResource extends JsonResource
{
    /**
     * Serializa las propiedades del Data Transfer Object en el formato de API requerido.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'parent_id'    => $this->parentId,
            'nivel'        => $this->nivel,
            'nombre'       => $this->nombre,
            'abreviatura'  => $this->abreviatura,
            'nombre_corto' => $this->nombreCorto,
            'rfc'          => $this->rfc,
            'encargado_id' => $this->encargadoId,
            'estado'       => $this->estado,
            'parent'       => $this->parent ? new self($this->parent) : null,
            'encargado'    => $this->encargado,
            'children'     => $this->children ? self::collection($this->children) : null,
        ];
    }
}