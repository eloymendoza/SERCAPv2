<?php

namespace App\Actions\Auth;

use App\DTOs\UserDTO;

class SyncSessionAction
{
    /**
     * Sincroniza los datos del DTO en la sesión de Laravel.
     */
    public function execute(UserDTO $dto): void
    {
        session()->put([
            'id'             => $dto->id,
            'idPersonal'     => $dto->idPersonal,
            'usuario'        => $dto->username,
            'nombreCompleto' => $dto->name,
            'email'          => $dto->email,
            'puestoActual'   => $dto->puestoActual,
            'permisos'       => $dto->permisos,
            'token'          => $dto->token,
            'rutaFoto'       => $dto->rutaFoto,
        ]);
    }
}
