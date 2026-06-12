<?php

namespace App\Domain\GestorCV\Enums;

/**
 * Estado del aspirante dentro del flujo de reclutamiento.
 */
enum EstadoAspirante: string
{
    case Nuevo       = 'nuevo';
    case EnRevision  = 'en_revision';
    case Reclutado   = 'reclutado';
    case Rechazado   = 'rechazado';
    case Contratado  = 'contratado';
 
    public function label(): string
    {
        return match($this) {
            self::Nuevo      => 'Nuevo',
            self::EnRevision => 'En revisión',
            self::Reclutado  => 'Reclutado',
            self::Rechazado  => 'Rechazado',
            self::Contratado => 'Contratado',
        };
    }
}