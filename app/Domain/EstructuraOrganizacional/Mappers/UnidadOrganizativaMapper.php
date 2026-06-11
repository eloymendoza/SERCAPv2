<?php

namespace App\Domain\EstructuraOrganizacional\Mappers;

use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;

class UnidadOrganizativaMapper
{
    /**
     * Transforma la entidad relacional Eloquent hacia el Data Transfer Object.
     */
    public function toDTO(UnidadOrganizativa $model): UnidadOrganizativaDTO
    {
        return new UnidadOrganizativaDTO(
            id: $model->id,
            parentId: $model->parent_id,
            nivel: $model->nivel,
            nombre: $model->nombre,
            abreviatura: $model->abreviatura,
            nombreCorto: $model->nombre_corto,
            rfc: $model->rfc,
            encargadoId: $model->encargado_id,
            estado: $model->estado,
            parent: $model->relationLoaded('parent') && $model->parent ? $this->toDTO($model->parent) : null,
            children: $model->relationLoaded('children') ? $model->children->map(fn($c) => $this->toDTO($c))->toArray() : null,
            encargado: $model->relationLoaded('encargado') && $model->encargado ? $model->encargado->toArray() : null
        );
    }

    /**
     * Extrae el array de mutación para persistencia filtrando nulos contextuales.
     */
    public function toPersistenceArray(UnidadOrganizativaDTO $dto): array
    {
        $data = [
            'parent_id' => $dto->parentId,
            'nivel' => $dto->nivel,
            'nombre' => $dto->nombre,
            'abreviatura' => $dto->abreviatura,
            'nombre_corto' => $dto->nombreCorto,
            'rfc' => $dto->rfc,
            'encargado_id' => $dto->encargadoId,
            'estado' => $dto->estado,
        ];
        
        // Se evita filtrar 'parent_id' o 'encargado_id' si explícitamente se mandan en null para borrar relacion
        // pero por regla general en updates se filtra null si no viene en payload. Para simplificar:
        return $data;
    }
}