<?php

namespace App\App\Api\EstructuraOrganizacional\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;

/**
 * @property UnidadOrganizativaDTO $resource
 */
class UnidadOrganizativaResource extends JsonResource
{
    /**
     * Transforma el DTO a un arreglo de respuesta serializable.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->resource->id,
            'parent_id'    => $this->resource->parentId,
            'nivel'        => $this->resource->nivel,
            'nombre'       => $this->resource->nombre,
            'abreviatura'  => $this->resource->abreviatura,
            'nombre_corto' => $this->resource->nombreCorto,
            'rfc'          => $this->resource->rfc,
            'encargado_id' => $this->resource->encargadoId,
            'estado'       => $this->resource->estado,
            'parent'       => $this->resource->parent ? new self($this->resource->parent) : null,
            'encargado'    => $this->resource->encargado,
            'children'     => $this->resource->children ? self::collection($this->resource->children) : null,
        ];
    }
}
