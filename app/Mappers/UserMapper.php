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
        $username = $data['usuario'] ?? '';
        
        return new UserDTO(
            id: null,
            idPersonal: isset($data['idPersonal']) ? (int) $data['idPersonal'] : null,
            username: $username,
            name: $data['nombreCompleto'] ?? '',
            email: $data['email'] ?? $this->generateDefaultEmail($username),
            puestoActual: $data['puestoActual'] ?? null,
            rutaFoto: $data['rutaFoto'] ?? null,
            permisos: $data['permisos'] ?? [],
            token: $data['token'] ?? null
        );
    }

    // Model → DTO
    public function toDTO(array|object $data): UserDTO {
        $data = (array) $data;
        $username = $data['usuario'] ?? $data['username'] ?? '';

        return new UserDTO(
            id: $data['id'] ?? null,
            idPersonal: isset($data['id_personal']) ? (int) $data['id_personal'] : (isset($data['idPersonal']) ? (int) $data['idPersonal'] : null),
            username: $username,
            name: $data['nombreCompleto'] ?? $data['name'] ?? '',
            email: $data['email'] ?? $this->generateDefaultEmail($username),
            puestoActual: $data['puestoActual'] ?? null,
            rutaFoto: $data['rutaFoto'] ?? null,
            permisos: $data['permisos'] ?? [],
            token: $data['token'] ?? null
        );
    }

    // DTO → Array para guardar en BD
    public function toPersistenceArray(UserDTO $dto): array {
        return [
            'id_personal' => $dto->idPersonal,
            'username'    => $dto->username,
            'name'        => $dto->name,
            'email'       => $dto->email,
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

    /**
     * Genera un email por defecto basado en el usuario y configuración.
     */
    private function generateDefaultEmail(string $username): string
    {
        $domain = config('services.django_auth.default_email_domain', 'grupo-iai.com.mx');
        return !empty($username) ? "{$username}@{$domain}" : "";
    }
}