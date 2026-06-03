<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Inyecta el request_id y los metadatos del request en el campo `extra` de cada LogRecord.
 *
 * Al estar registrado como processor en el handler del DynamicLogger, garantiza que
 * TODAS las líneas de log de un flujo lleven el contexto de traza sin que el
 * desarrollador lo pase manualmente en cada llamada.
 */
class RequestIdProcessor implements ProcessorInterface
{
    public function __construct(private readonly LogContext $logContext) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        if (!$this->logContext->isInitialized()) {
            return $record;
        }

        return $record->with(extra: array_merge(
            $record->extra,
            $this->logContext->baseContext()
        ));
    }
}
