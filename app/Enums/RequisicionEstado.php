<?php

namespace App\Enums;

/**
 * Define los estados automáticos de una requisición (Folio Padre) calculados según sus vacantes y postulaciones.
 */
enum RequisicionEstado: string
{
    /**
     * El 100% de las vacantes de la requisición están bloqueadas sin perfil.
     */
    case PENDIENTE_PERFIL = 'pendiente_perfil';

    /**
     * Al menos una vacante en búsqueda activa sin postulaciones activas compitiendo.
     */
    case ABIERTA = 'abierta';

    /**
     * Al menos una postulación en entrevista, exámenes o autorización excepcional.
     */
    case EN_PROCESO = 'en_proceso';

    /**
     * Al menos una vacante contratada existiendo otras pendientes por cubrir.
     */
    case CIERRE_PARCIAL = 'cierre_parcial';

    /**
     * Todas las vacantes en Seleccionada y al menos la última de ellas en Auditoría (Anexo D).
     */
    case VALIDACION_ADMINISTRATIVA = 'validacion_administrativa';

    /**
     * El 100% de las vacantes contratadas o canceladas.
     */
    case CUBIERTA = 'cubierta';

    /**
     * El 100% de las vacantes canceladas.
     */
    case CANCELADA = 'cancelada';

    /**
     * Retorna el nombre amigable del estado para la vista.
     */
    public function label(): string
    {
        return match($this) {
            self::PENDIENTE_PERFIL => 'Pendiente de Perfil',
            self::ABIERTA => 'Abierta (Activa)',
            self::EN_PROCESO => 'En Proceso',
            self::CIERRE_PARCIAL => 'Cierre Parcial',
            self::VALIDACION_ADMINISTRATIVA => 'Validación Administrativa',
            self::CUBIERTA => 'Cubierta (Cerrada)',
            self::CANCELADA => 'Cancelada',
        };
    }

    /**
     * Retorna la clase de color estándar asociada al estado.
     */
    public function color(): string
    {
        return match($this) {
            self::PENDIENTE_PERFIL => 'light',
            self::ABIERTA => 'info',
            self::EN_PROCESO => 'primary',
            self::CIERRE_PARCIAL => 'dark',
            self::VALIDACION_ADMINISTRATIVA => 'warning',
            self::CUBIERTA => 'success',
            self::CANCELADA => 'danger',
        };
    }
}