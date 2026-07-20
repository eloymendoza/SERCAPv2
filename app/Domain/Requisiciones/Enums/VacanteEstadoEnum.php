<?php

namespace App\Domain\Requisiciones\Enums;

/**
 * Define los estados de una vacante (asiento/silla presupuestal) en el proceso de contratación.
 */
enum VacanteEstadoEnum: string
{
    /**
     * Perfil (Anexo A) inexistente. El asiento está bloqueado con un SLA de 3 días hábiles.
     */
    case PENDIENTE_PERFIL = 'pendiente_perfil';

    /**
     * Puesto existente que carece de vinculación local con el ID del documento en el SGC.
     */
    case PENDIENTE_VINCULACION_SGC = 'pendiente_vinculacion_sgc';

    /**
     * Perfil vigente y asiento vacío disponible para postulaciones.
     */
    case BUSQUEDA_ACTIVA = 'busqueda_activa';

    /**
     * Candidato asociado al asiento mediante el registro del Anexo B.
     */
    case SELECCIONADA = 'seleccionada';

    /**
     * Auditoría del movimiento contractual (Anexo D). Puede retroceder ante inconsistencias.
     */
    case EN_AUDITORIA = 'en_auditoria';

    /**
     * Ciclo concluido con firma de contrato, validación de Anexo C y alta en el IMSS.
     */
    case CONTRATADA = 'contratada';

    /**
     * Plaza cancelada por solicitud del área, descartando postulaciones vinculadas.
     */
    case CANCELADA = 'cancelada';

    /**
     * Retorna el nombre amigable del estado para la vista.
     */
    public function label(): string
    {
        return match($this) {
            self::PENDIENTE_PERFIL => 'Pendiente de Perfil',
            self::PENDIENTE_VINCULACION_SGC => 'Pendiente Vinculación SGC',
            self::BUSQUEDA_ACTIVA => 'Búsqueda Activa',
            self::SELECCIONADA => 'Seleccionada (Anexo B)',
            self::EN_AUDITORIA => 'En Auditoría (Anexo D)',
            self::CONTRATADA => 'Contratada (Cerrada)',
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
            self::PENDIENTE_VINCULACION_SGC => 'warning',
            self::BUSQUEDA_ACTIVA => 'info',
            self::SELECCIONADA => 'dark',
            self::EN_AUDITORIA => 'warning',
            self::CONTRATADA => 'success',
            self::CANCELADA => 'danger',
        };
    }
}