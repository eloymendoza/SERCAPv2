<?php

namespace App\Domain\Proyectos\Mappers;

use App\Domain\Proyectos\Models\Proyecto;
use App\Domain\Proyectos\DTOs\ProyectoDTO;

class ProyectoMapper
{
    /**
     * Mapea un modelo de Eloquent hacia su DTO de salida.
     */
    public function toDTO(Proyecto $model): ProyectoDTO
    {
        return new ProyectoDTO(
            id: $model->idProyecto,
            proyecto: $model->proyecto,
            descripcion: $model->descripcion,
            jefeProyecto: $model->jefeProyecto,
            activoProyecto: $model->activoProyecto,
        );
    }
}