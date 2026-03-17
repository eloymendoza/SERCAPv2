<?php

namespace App\Mappers;

use App\DTOs\UserDTO;

class UserMapper
{
    /**
     * Transforma la respuesta cruda de la API de Django en un UserDTO.
     */
    public function fromDjangoToDTO(array $data): UserDTO
    {
        return new UserDTO(
            id: $data['idPersonal'] ?? null,
            username: $data['usuario'] ?? '',
            name: $data['nombreCompleto'] ?? '',
            email: ($data['usuario'] ?? 'user') . '@grupo-iai.com.mx'
        );
    }

    // Model → DTO
    public function toDTO(array|object $data): UserDTO {
        $data = (array) $data;
        return new UserDTO(
            id: $data['idPersonal'] ?? $data['id'] ?? null,
            username: $data['usuario'] ?? $data['username'] ?? '',
            name: $data['nombreCompleto'] ?? $data['name'] ?? '',
            email: $data['email'] ?? '',
            puestoActual: $data['puestoActual'] ?? null,
            rutaFoto: $data['rutaFoto'] ?? null,
            permisos: $data['permisos'] ?? []
        );
    }

    // DTO → Array para guardar en BD
    public function toPersistenceArray(UserDTO $dto): array {
        return [
            'id'       => $dto->id,
            'username' => $dto->username,
            'name'     => $dto->name,
            'email'    => $dto->email,
        ];
    }

    // DTO → Array para respuesta HTTP
    public function toResponseArray(UserDTO $dto): array {
        return [
            'idPersonal'     => $dto->id,
            'usuario'        => $dto->username,
            'nombreCompleto' => $dto->name,
            'puestoActual'   => $dto->puestoActual,
            'rutaFoto'       => $dto->rutaFoto,
            'permisos'       => $dto->permisos,
        ];
    }

    // Múltiples Models → DTOs
    public function toDTOCollection(iterable $models): array {
        $dtos = [];
        foreach ($models as $model) {
            $dtos[] = $this->toDTO($model);
        }
        return $dtos;
    }
}