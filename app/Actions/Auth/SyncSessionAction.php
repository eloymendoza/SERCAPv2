<?php

namespace App\Actions\Auth;

class SyncSessionAction
{
    /**
     * Sincroniza los datos de la API de Django en la sesión de Laravel.
     */
    public function execute(array $data): void
    {
        session()->put([
            'idPersonal'     => $data['idPersonal'] ?? null,
            'usuario'        => $data['usuario'] ?? null,
            'nombreCompleto' => $data['nombreCompleto'] ?? null,
            'puestoActual'   => $data['puestoActual'] ?? null,
            'permisos'       => $data['permisos'] ?? [],
            'token'          => $data['token'] ?? null,
            'rutaFoto'       => $data['rutaFoto'] ?? null,
        ]);
    }
}
