<?php

namespace App\Domain\Requisiciones\Enums;

/**
 * Define los estados del flujo de aprobación para una solicitud de requisición.
 */
enum SolicitudRequisicionEstadoEnum: string
{
    /**
     * Solicitud creada pero no enviada a aprobación.
     */
    case BORRADOR = 'borrador';

    /**
     * Solicitud activa en revisión dentro del flujo de trabajo (workflow).
     */
    case EN_PROCESO = 'en_proceso';

    /**
     * Solicitud denegada por algún aprobador del flujo.
     */
    case RECHAZADO = 'rechazado';

    /**
     * Solicitud anulada por el área solicitante o administrador.
     */
    case CANCELADO = 'cancelado';

    /**
     * Solicitud aprobada completamente, lista para originar la requisición.
     */
    case TERMINADO = 'terminado';

    /**
     * Retorna el nombre amigable del estado para la vista.
     */
    public function label(): string
    {
        return match($this) {
            self::BORRADOR => 'Borrador',
            self::EN_PROCESO => 'En Proceso',
            self::RECHAZADO => 'Rechazado',
            self::CANCELADO => 'Cancelado',
            self::TERMINADO => 'Terminado',
        };
    }

    /**
     * Retorna la clase de color estándar asociada al estado.
     */
    public function color(): string
    {
        return match($this) {
            self::BORRADOR => 'secondary',
            self::EN_PROCESO => 'primary',
            self::RECHAZADO => 'danger',
            self::CANCELADO => 'dark',
            self::TERMINADO => 'success',
        };
    }
}