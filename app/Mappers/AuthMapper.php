<?php

namespace App\Mappers;

use App\DTOs\AuthDTO;
use App\Http\Requests\LoginRequest;

/**
 * Mapeo de datos para autenticación.
 */
class AuthMapper
{
    /**
     * Convierte una solicitud de login en un AuthDTO.
     * @param LoginRequest $request Solicitud de login.
     * @return AuthDTO DTO de autenticación.
     */
    public function toDTO(LoginRequest $request): AuthDTO {
        return new AuthDTO(
            username: $request->validated('username'),
            password: base64_encode(trim($request->validated('password')))
        );
    }

    /**
     * Convierte un AuthDTO en un array para persistencia.
     * @param AuthDTO $dto DTO de autenticación.
     * @return array Array para persistencia.
     */
    public function toPersistenceArray(AuthDTO $dto): array {
        return [
            'username' => $dto->username,
            'password' => $dto->password,
        ];
    }

    /**
     * Convierte una colección de requests en una colección de DTOs.
     * @param iterable $requests Colección de solicitudes de login.
     * @return array<AuthDTO> Colección de DTOs de autenticación.
     */
    public function toDTOCollection(iterable $requests): array {
        $dtos = [];
        foreach ($requests as $request) {
            $dtos[] = self::toDTO($request);
        }
        return $dtos;
    }
}