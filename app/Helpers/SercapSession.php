<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class SercapSession
{
    /**
     * Obtiene el token de autenticación de Django (tkg).
     */
    public static function getDjangoToken(): ?string
    {
        return Session::get('tkg');
    }

    /**
     * Obtiene la lista de permisos del usuario.
     */
    public static function getPermissions(): array
    {
        return Session::get('permisos', []);
    }

    /**
     * Verifica si el usuario tiene un permiso específico.
     */
    public static function hasPermission(string $permission): bool
    {
        return in_array($permission, self::getPermissions());
    }

    /**
     * Obtiene el ID del personal en Django.
     */
    public static function getPersonalId(): ?int
    {
        return Session::get('idPersonal');
    }

    /**
     * Obtiene la ruta de la foto de perfil.
     */
    public static function getProfilePhoto(): ?string
    {
        return Session::get('rutaFoto');
    }
}
