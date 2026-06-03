<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class SensitiveDataProcessor implements ProcessorInterface
{
    /** Campos cuyo valor será enmascarado en los logs. */
    protected array $camposSensibles = [
        'token',
        'password',
        'secret',
    ];

    protected string $reemplazo = '[PROTEGIDO]';

    public function __invoke(LogRecord $record): LogRecord
    {
        return new LogRecord(
            datetime: $record->datetime,
            channel:  $record->channel,
            level:    $record->level,
            message:  is_string($record->message)
                          ? $this->enmascararEnTexto($record->message)
                          : $record->message,
            context:  !empty($record->context) ? $this->enmascarar($record->context) : $record->context,
            extra:    !empty($record->extra)   ? $this->enmascarar($record->extra)   : $record->extra,
        );
    }

    /** Enmascara datos sensibles en arrays y objetos. */
    protected function enmascarar(mixed $data): mixed
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($this->esSensible($key)) {
                    $data[$key] = $this->reemplazo;
                } elseif (is_array($value)) {
                    $data[$key] = $this->enmascarar($value);
                }
                // Los objetos (Enums, DTOs) se dejan intactos para evitar problemas de clonación.
            }
            return $data;
        }

        if (is_object($data)) {
            $resultado = [];
            foreach (get_object_vars($data) as $key => $value) {
                $resultado[$key] = $this->esSensible($key) ? $this->reemplazo : $value;
            }
            return $resultado;
        }

        return $data;
    }

    /** Enmascara patrones "campo: valor" y "campo=valor" dentro de strings. */
    protected function enmascararEnTexto(string $mensaje): string
    {
        foreach ($this->camposSensibles as $campo) {
            $patrones = [
                "/\b{$campo}\s*[:=]\s*[^\s,}\]]+/i",
                "/['\"]?{$campo}['\"]?\s*[:=]\s*['\"]?[^'\"}\],\s]+['\"]?/i",
            ];

            foreach ($patrones as $patron) {
                $mensaje = preg_replace_callback(
                    $patron,
                    fn($m) => preg_replace('/[:=]\s*.+/', ': ' . $this->reemplazo, $m[0]),
                    $mensaje
                );
            }
        }

        return $mensaje;
    }

    protected function esSensible(string $key): bool
    {
        $key = strtolower($key);

        foreach ($this->camposSensibles as $campo) {
            if (stripos($key, $campo) !== false) {
                return true;
            }
        }

        return false;
    }

    public function agregarCampoSensible(string $campo): static
    {
        $this->camposSensibles[] = strtolower($campo);
        return $this;
    }

    public function establecerReemplazo(string $reemplazo): static
    {
        $this->reemplazo = $reemplazo;
        return $this;
    }
}
