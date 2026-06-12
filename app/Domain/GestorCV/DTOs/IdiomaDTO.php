<?php

namespace App\Domain\GestorCV\DTOs;

/**
 * Contenedor de datos inmutable para los idiomas de un aspirante.
 */
class IdiomaDTO
{
    public function __construct(
        public readonly int    $idiomaId,
        public readonly string $nivel,
    ) {}
 
    public function toArray(): array
    {
        return [
            'idioma_id' => $this->idiomaId,
            'nivel'     => $this->nivel,
        ];
    }
 
    public static function fromArray(array $data): self
    {
        return new self(
            idiomaId: (int) $data['idiomaId'],
            nivel:    $data['nivel'],
        );
    }
}