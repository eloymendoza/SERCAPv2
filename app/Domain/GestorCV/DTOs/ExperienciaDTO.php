<?php

namespace App\Domain\GestorCV\DTOs;

/**
 * Contenedor de datos inmutable para la experiencia laboral de un aspirante.
 */
class ExperienciaDTO
{
    public function __construct(
        public readonly string  $cargo,
        public readonly string  $nombreEmpresa,
        public readonly bool    $trabajoActual,
        public readonly string  $fechaInicio,
        public readonly ?string $fechaFin,
        public readonly ?string $responsabilidades,
    ) {}
 
    public function toArray(): array
    {
        return [
            'cargo'             => $this->cargo,
            'nombre_empresa'    => $this->nombreEmpresa,
            'trabajo_actual'    => $this->trabajoActual,
            'fecha_inicio'      => $this->fechaInicio,
            'fecha_fin'         => $this->fechaFin,
            'responsabilidades' => $this->responsabilidades,
        ];
    }
 
    public static function fromArray(array $data): self
    {
        return new self(
            cargo:             $data['cargo'],
            nombreEmpresa:     $data['nombreEmpresa'],
            trabajoActual:     (bool) $data['trabajoActual'],
            fechaInicio:       $data['fechaInicio'],
            fechaFin:          $data['fechaFin'] ?? null,
            responsabilidades: $data['responsabilidades'] ?? null,
        );
    }
}