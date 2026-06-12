<?php

namespace App\Domain\GestorCV\DTOs;

/**
 * Contenedor de datos inmutable para los conocimientos técnicos de un aspirante.
 */
class ConocimientoTecnicoDTO
{
    public function __construct(
        public readonly string  $nombre,
        public readonly ?string $categoria,
    ) {}
 
    public function toArray(): array
    {
        return [
            'nombre'    => $this->nombre,
            'categoria' => $this->categoria ?? 'sin_clasificar',
        ];
    }
 
    public static function fromArray(array $data): self
    {
        return new self(
            nombre:    $data['nombre'],
            categoria: $data['categoria'] ?? null,
        );
    }
}