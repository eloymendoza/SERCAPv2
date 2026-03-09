<?php

namespace App\Mappers;

use App\Models\User;

class UserMapper
{
    /**
     * Transforma la respuesta cruda de la API de Django en un array 
     * compatible con los atributos del modelo User local.
     */
    public static function fromDjangoToLocal(array $data): array
    {
        return [
            'id'       => $data['idPersonal'],
            'username' => $data['usuario'],
            'name'     => $data['nombreCompleto'],
            'email'    => ($data['usuario'] ?? 'user') . '@grupo-iai.com.mx',
        ];
    }

    /**
     * (Opcional) Transforma un modelo User a un formato de respuesta estándar.
     */
    public static function toResponse(User $user, array $extraData = []): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'username' => $user->username,
            'rutaFoto' => $extraData['rutaFoto'] ?? null,
        ];
    }
}