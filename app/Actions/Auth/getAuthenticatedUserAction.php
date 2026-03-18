<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Session;

/**
 * Recuperación de metadatos de sesión del usuario.
 */
class GetAuthenticatedUserAction
{
    /**
     * @return array
     */
    public function execute(): array
    {
        return [
            'id'             => Session::get('id'),
            'idPersonal'     => Session::get('idPersonal'),
            'usuario'        => Session::get('usuario'),
            'nombreCompleto' => Session::get('nombreCompleto'),
            'email'          => Session::get('email'),
            'puestoActual'   => Session::get('puestoActual'),
            'rutaFoto'       => Session::get('rutaFoto'),
            'permisos'       => Session::get('permisos', []),
        ];
    }
}