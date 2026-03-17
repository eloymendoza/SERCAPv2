<?php

namespace App\DTOs;

class UserDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $username,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $puestoActual = null,
        public readonly ?string $rutaFoto = null,
        public readonly array $permisos = []
    ) {}

    public function toArray(): array {
        return [
            'id'             => $this->id,
            'username'       => $this->username,
            'name'           => $this->name,
            'email'          => $this->email,
            'puestoActual'   => $this->puestoActual,
            'rutaFoto'       => $this->rutaFoto,
            'permisos'       => $this->permisos,
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            id: $data['id'] ?? null,
            username: $data['username'] ?? '',
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            puestoActual: $data['puestoActual'] ?? null,
            rutaFoto: $data['rutaFoto'] ?? null,
            permisos: $data['permisos'] ?? []
        );
    }
}
