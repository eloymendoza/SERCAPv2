<?php

namespace App\Exceptions\Infrastructure;

use App\Exceptions\BaseApiException;

/**
 * Clase base para fallos técnicos o de infraestructura.
 * 
 * Estas excepciones se registran automáticamente en los logs para
 * facilitar el diagnóstico y monitoreo de errores críticos.
 */
abstract class InfrastructureException extends BaseApiException
{
    /**
     * Delega el reporte al manejador por defecto de Laravel (Log activo).
     * 
     * @return void
     */
    public function report(): void
    {
        // El comportamiento por defecto es reportar el error en logs.
    }
}
