<?php

namespace App\Domain\GestorCV\DTOs;

/**
 * Contenedor de datos inmutable para los certificados de un aspirante.
 */
class CertificadoDTO
{
    public function __construct(
        public readonly string  $nombre,
        public readonly ?string $institucion,
        public readonly ?int    $anioFin,
    ) {}
 
    public function toArray(): array
    {
        return [
            'nombre'      => $this->nombre,
            'institucion' => $this->institucion,
            'anio_fin'    => $this->anioFin,
        ];
    }
 
    public static function fromArray(array $data): self
    {
        return new self(
            nombre:      $data['nombre'],
            institucion: $data['institucion'] ?? null,
            anioFin:     isset($data['anioFin']) ? (int) $data['anioFin'] : null,
        );
    }
}