<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

/**
 * Formatea cada registro de log como un JSON estructurado de una sola línea.
 *
 * Implementa las reglas específicas para la ingesta nativa en Promtail/Grafana Loki.
 */
class StructuredJsonFormatter extends JsonFormatter
{
    /**
     * Formatea un registro de log a JSON estructurado.
     */
    public function format(LogRecord $record): string
    {
        $normalized = $this->normalizeRecord($record);

        $extra = (array) ($normalized['extra'] ?? []);
        $context = (array) ($normalized['context'] ?? []);

        $payload = array_merge($extra, $context, [
            'timestamp' => $record->datetime->format(\DateTimeInterface::ATOM),
            'level'     => $record->level->getName(),
            'module'    => $record->channel,
            'message'   => $record->message,
        ]);

        unset($payload['app']);

        return $this->toJson($payload, true) . ($this->appendNewline ? "\n" : '');
    }
}
