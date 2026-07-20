<?php

namespace App\Domain\Requisiciones\Enums;

/**
 * Define los estados del embudo de reclutamiento para una postulación individual.
 */
enum PostulacionEstadoEnum: string
{
    /**
     * Candidato apto para iniciar el proceso tras evaluación curricular.
     */
    case PRESELECCIONADO = 'preseleccionado';

    /**
     * Canalizado a entrevista técnica. Inicia SLA: 3 días hábiles (Nuevo Ingreso) o 5 (Reingreso).
     */
    case EN_ENTREVISTA_TECNICA = 'en_entrevista_tecnica';

    /**
     * Coordinación de exámenes médicos, clínicos o psicométricos.
     */
    case EN_EXAMENES = 'en_examenes';

    /**
     * Estado condicional por "Cumplimiento con Reservas" en competencias blandas.
     */
    case AUTORIZACION_EXCEPCIONAL = 'autorizacion_excepcional';

    /**
     * Candidato no apto en algún filtro o sin disponibilidad.
     */
    case RECHAZADO = 'rechazado';

    /**
     * Ganador del proceso operativo con Vo.Bo. Final para relación 1:1 con la vacante.
     */
    case SELECCIONADO = 'seleccionado';

    /**
     * Retorna el nombre amigable del estado para la vista.
     */
    public function label(): string
    {
        return match($this) {
            self::PRESELECCIONADO => 'Preseleccionado',
            self::EN_ENTREVISTA_TECNICA => 'En Entrevista Técnica',
            self::EN_EXAMENES => 'En Exámenes',
            self::AUTORIZACION_EXCEPCIONAL => 'Autorización Excepcional',
            self::RECHAZADO => 'Rechazado',
            self::SELECCIONADO => 'Seleccionado (Vo.Bo. Final)',
        };
    }

    /**
     * Retorna la clase de color estándar asociada al estado.
     */
    public function color(): string
    {
        return match($this) {
            self::PRESELECCIONADO => 'info',
            self::EN_ENTREVISTA_TECNICA => 'primary',
            self::EN_EXAMENES => 'warning',
            self::AUTORIZACION_EXCEPCIONAL => 'dark',
            self::RECHAZADO => 'danger',
            self::SELECCIONADO => 'success',
        };
    }
}