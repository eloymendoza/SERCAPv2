<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Session;

class GetAuthenticatedUserAction
{
    /**
     * Extrae los metadatos de la sesión actual de Laravel.
     */
    public function execute(): array
    {
        return [
            'idPersonal'     => Session::get('idPersonal'),
            'usuario'        => Session::get('usuario'),
            'nombreCompleto' => Session::get('nombreCompleto'),
            'puestoActual'   => Session::get('puestoActual'),
            'rutaFoto'       => Session::get('rutaFoto'),
            'permisos'       => Session::get('permisos', []),
        ];
    }
}