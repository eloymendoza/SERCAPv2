<?php

namespace App\Mappers;

use App\DTOs\AuthDTO;
use App\Http\Requests\LoginRequest;

class AuthMapper
{
    /**
     * Transforma una LoginRequest en un AuthDTO,
     * aplicando el encoding Base64 a la contraseña para la API de Django.
     */
    public static function toAuthDTO(LoginRequest $request): AuthDTO
    {
        return new AuthDTO(
            username: $request->validated('username'),
            password: base64_encode(trim($request->validated('password')))
        );
    }
}
