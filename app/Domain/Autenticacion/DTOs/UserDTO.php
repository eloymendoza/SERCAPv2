<?php

namespace App\Domain\Autenticacion\DTOs;

use JsonSerializable;

class UserDTO implements JsonSerializable
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $idPersonal,
        public readonly string $username,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $puestoActual = null,
        public readonly ?string $rutaFoto = null,
        public readonly array $permisos = [],
        public readonly ?string $token = null
    ) {}

    public function toArray(): array {
        return [
            'id'             => $this->id,
            'idPersonal'     => $this->idPersonal,
            'username'       => $this->username,
            'name'           => $this->name,
            'email'          => $this->email,
            'puestoActual'   => $this->puestoActual,
            'rutaFoto'       => $this->rutaFoto,
            'permisos'       => $this->permisos,
            'token'          => $this->token,
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            id: $data['id'] ?? null,
            idPersonal: $data['idPersonal'] ?? null,
            username: $data['username'] ?? '',
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            puestoActual: $data['puestoActual'] ?? null,
            rutaFoto: $data['rutaFoto'] ?? null,
            permisos: $data['permisos'] ?? [],
            token: $data['token'] ?? null
        );
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    /**
     * Crea una copia del DTO con un nuevo ID local.
     */
    public function withId(int $id): self {
        return new self(
            id: $id,
            idPersonal: $this->idPersonal,
            username: $this->username,
            name: $this->name,
            email: $this->email,
            puestoActual: $this->puestoActual,
            rutaFoto: $this->rutaFoto,
            permisos: $this->permisos,
            token: $this->token
        );
    }
}