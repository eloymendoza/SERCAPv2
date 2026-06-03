<?php

namespace App\Domain\GestorCV\DTOs;

/**
 * Contenedor de datos inmutable para la educación de un aspirante.
 */
class EducacionDTO
{
    public function __construct(
        public readonly string  $institucion,
        public readonly int     $nivelEstudioId,
        public readonly string  $titulo,
        public readonly string  $estadoEducacion,
        public readonly ?int    $anioFin,
    ) {}
 
    public function toArray(): array
    {
        return [
            'institucion'      => $this->institucion,
            'nivel_estudio_id' => $this->nivelEstudioId,
            'titulo'           => $this->titulo,
            'estado_educacion' => $this->estadoEducacion,
            'anio_fin'         => $this->anioFin,
        ];
    }
 
    public static function fromArray(array $data): self
    {
        return new self(
            institucion:     $data['institucion'],
            nivelEstudioId:  (int) $data['nivelEstudioId'],
            titulo:          $data['titulo'],
            estadoEducacion: $data['estadoEducacion'],
            anioFin:         isset($data['anioFin']) ? (int) $data['anioFin'] : null,
        );
    }
}