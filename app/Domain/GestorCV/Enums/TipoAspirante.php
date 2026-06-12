<?php

namespace App\Domain\GestorCV\Enums;

/**
 * Origen del registro del aspirante.
 * Determina si se debe buscar información previa en sistemas legacy.
 */
enum TipoAspirante: string
{
    case NuevoAspirante    = 'nuevo_aspirante';
    case PersonalActivo    = 'personal_activo';
    case PersonalAnterior  = 'personal_anterior';
 
    public function label(): string
    {
        return match($this) {
            self::NuevoAspirante   => 'Nuevo aspirante',
            self::PersonalActivo   => 'Personal activo',
            self::PersonalAnterior => 'Personal anterior',
        };
    }
 
    /**
     * Indica si este tipo requiere buscar Id_personal en el sistema legacy.
     */
    public function requiereIdPersonal(): bool
    {
        return match($this) {
            self::NuevoAspirante  => false,
            self::PersonalActivo,
            self::PersonalAnterior => true,
        };
    }
}