<?php

namespace App\Domain\Puestos\DTOs;

class PerfilSgcDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $identificacion,
        public readonly string $titulo,
        public readonly int $revision,
        public readonly string $estado
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            identificacion: $data['identificacion'] ?? '',
            titulo: $data['titulo'] ?? '',
            revision: $data['revision'] ?? 0,
            estado: $data['estado'] ?? ''
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'identificacion' => $this->identificacion,
            'titulo' => $this->titulo,
            'revision' => $this->revision,
            'estado' => $this->estado,
        ];
    }
}