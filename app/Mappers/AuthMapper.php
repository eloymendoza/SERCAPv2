<?php

namespace App\Mappers;

use App\DTOs\AuthDTO;
use App\Http\Requests\LoginRequest;

class AuthMapper
{
    // Model → DTO (No hay modelo Auth directo, pero se usa para DTO)
    public function toDTO(LoginRequest $request): AuthDTO {
        return new AuthDTO(
            username: $request->validated('username'),
            password: base64_encode(trim($request->validated('password')))
        );
    }

    // DTO → Array para guardar en BD
    public function toPersistenceArray(AuthDTO $dto): array {
        return [
            'username' => $dto->username,
            'password' => $dto->password,
        ];
    }

    // Múltiples Models → DTOs
    public function toDTOCollection(iterable $requests): array {
        $dtos = [];
        foreach ($requests as $request) {
            $dtos[] = self::toDTO($request);
        }
        return $dtos;
    }
}