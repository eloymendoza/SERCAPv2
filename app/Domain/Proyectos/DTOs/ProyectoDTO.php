<?php

namespace App\Domain\Proyectos\DTOs;

readonly class ProyectoDTO
{
    public function __construct(
        public int $id,
        public string $proyecto,
        public ?string $descripcion,
        public ?string $jefeProyecto,
        public ?bool $activoProyecto,
    ) {}
}