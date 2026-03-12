<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\File;
use Monolog\Processor\ProcessorInterface;

class DynamicLogger
{
    public function __invoke(array $config)
    {
        $channelName = $config['name'] ?? 'default';
        $basePath = storage_path('logs/' . ucfirst($channelName));

        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        $filePath = $basePath . '/' . strtolower($channelName) . '_' . date('d-m-Y') . '.log';

        $logger = new Logger($channelName);
        $logger->pushHandler(new StreamHandler($filePath, Logger::toMonologLevel(env('LOG_LEVEL', 'debug'))));

        return $logger;
    }
}

class SensitiveDataProcessor implements ProcessorInterface
{
    /**
     * Campos sensibles que serán ocultados en los logs
     */
    protected array $camposSensibles = [
        'token',
    ];

    /**
     * Patrón de reemplazo para datos sensibles
     */
    protected string $reemplazo = '[PROTEGIDO]';

    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;
        $extra = $record->extra;
        $message = $record->message;

        if (!empty($context)) {
            $context = $this->enmascararDatosSensibles($context);
        }

        if (!empty($extra)) {
            $extra = $this->enmascararDatosSensibles($extra);
        }

        if (is_string($message)) {
            $message = $this->enmascararDatosSensiblesEnTexto($message);
        }

        return new LogRecord(
            datetime: $record->datetime,
            channel: $record->channel,
            level: $record->level,
            message: $message,
            context: $context,
            extra: $extra
        );
    }

    /**
     * Oculta datos sensibles en arrays y objetos
     */
    protected function enmascararDatosSensibles($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($this->esCampoSensible($key)) {
                    $data[$key] = $this->reemplazo;
                } elseif (is_array($value)) {
                    $data[$key] = $this->enmascararDatosSensibles($value);
                } elseif (is_object($value)) {
                    // No procesamos objetos dentro de arrays, los dejamos como están
                    // Esto evita problemas con Enums, DTOs, y otros objetos no clonables
                    continue;
                }
            }
        } elseif (is_object($data)) {
            // Para objetos, los convertimos a array para procesarlos
            // Esto evita problemas con clone en Enums y objetos no clonables
            $dataArray = [];
            foreach (get_object_vars($data) as $key => $value) {
                if ($this->esCampoSensible($key)) {
                    $dataArray[$key] = $this->reemplazo;
                } elseif (is_array($value)) {
                    $dataArray[$key] = $this->enmascararDatosSensibles($value);
                } else {
                    $dataArray[$key] = $value;
                }
            }
            return $dataArray;
        }

        return $data;
    }

    /**
     * Oculta datos sensibles en strings (para patrones como "token: xxx")
     */
    protected function enmascararDatosSensiblesEnTexto(string $mensaje): string
    {
        foreach ($this->camposSensibles as $campo) {
            // Patrón para encontrar "campo: valor" o "campo=valor"
            $patrones = [
                "/\b{$campo}\s*[:=]\s*[^\s,}\]]+/i",
                "/['\"]?{$campo}['\"]?\s*[:=]\s*['\"]?[^'\"}\],\s]+['\"]?/i",
            ];

            foreach ($patrones as $patron) {
                $mensaje = preg_replace_callback($patron, function ($matches) use ($campo) {
                    return preg_replace('/[:=]\s*.+/', ': ' . $this->reemplazo, $matches[0]);
                }, $mensaje);
            }
        }

        return $mensaje;
    }

    /**
     * Verifica si un campo es sensible
     */
    protected function esCampoSensible(string $key): bool
    {
        $key = strtolower($key);

        foreach ($this->camposSensibles as $campoSensible) {
            if (stripos($key, $campoSensible) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Permite agregar campos sensibles personalizados
     */
    public function agregarCampoSensible(string $campo): self
    {
        $this->camposSensibles[] = strtolower($campo);
        return $this;
    }

    /**
     * Permite personalizar el texto de reemplazo
     */
    public function establecerReemplazo(string $reemplazo): self
    {
        $this->reemplazo = $reemplazo;
        return $this;
    }
}