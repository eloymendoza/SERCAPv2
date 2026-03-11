<?php

namespace App\Actions\Auth;

class SyncSessionAction
{
    /**
     * Sincroniza los datos de la API de Django en la sesión de Laravel.
     */
    public function execute(array $data): void
    {
        session([
            'permisos'       => $data['permisos'] ?? [],
            'tkg'            => $data['tkg'] ?? null,
            'idPersonal'     => $data['idPersonal'] ?? null,
            'nombreCompleto' => $data['nombreCompleto'] ?? null,
            'rutaFoto'       => $data['rutaFoto'] ?? null
        ]);
    }
}
