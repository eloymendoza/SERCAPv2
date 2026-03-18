<?php

namespace App\Mappers;

use App\DTOs\UserDTO;

/**
 * Mapeo de identidad y datos de perfil de usuario.
 */
class UserMapper
{
    /**
     * Convierte una respuesta cruda de API Auth en un UserDTO.
     * @param array $data Respuesta cruda de API Auth.
     * @return UserDTO
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

    /**
     * Convierte un array o objeto en un UserDTO.
     * @param array|object $data
     * @return UserDTO
     */
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

    /**
     * Convierte un UserDTO en un array para persistencia.
     * @param UserDTO $dto
     * @return array Estructura para persistencia local.
     */
    public function toPersistenceArray(UserDTO $dto): array {
        return [
            'id_personal' => $dto->idPersonal,
            'username'    => $dto->username,
            'name'        => $dto->name,
            'email'       => $dto->email,
        ];
    }

    /**
     * Convierte una colección de modelos en una colección de DTOs.
     * @param iterable $models
     * @return array<UserDTO>
     */
    public function toDTOCollection(iterable $models): array {
        $dtos = [];
        foreach ($models as $model) {
            $dtos[] = $this->toDTO($model);
        }
        return $dtos;
    }

    /**
     * Genera un email por defecto basado en el usuario y configuración.
     * @param string $username
     * @return string
     */
    private function generateDefaultEmail(string $username): string
    {
        $domain = config('services.django_auth.default_email_domain', 'grupo-iai.com.mx');
        return !empty($username) ? "{$username}@{$domain}" : "";
    }
}