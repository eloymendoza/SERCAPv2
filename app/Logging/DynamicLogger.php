<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Illuminate\Support\Facades\File;

/**
 * Fábrica de loggers Monolog con rotación diaria y enriquecimiento de contexto.
 *
 * Genera un logger por canal con las siguientes características:
 *   - Archivo rotativo diario bajo storage/logs/{Canal}/{canal}_YYYY-MM-DD.log
 *   - SensitiveDataProcessor: enmascara campos sensibles (token, password, secret)
 *   - RequestIdProcessor: inyecta request_id y metadatos del request en cada entry
 *
 * Configuración en config/logging.php:
 *   'driver' => 'custom'
 *   'via'    => \App\Logging\DynamicLogger::class
 *   'name'   => 'auth' | 'requisicion' | 'app' | ...
 */
class DynamicLogger
{
    public function __construct(
        private readonly LogContext $logContext,
        private readonly SensitiveDataProcessor $sensitiveProcessor,
    ) {}

    public function __invoke(array $config): Logger
    {
        $channelName = $config['name'] ?? 'app';
        $level       = $config['level'] ?? env('LOG_LEVEL', 'debug');
        $maxFiles    = (int) ($config['days'] ?? env('LOG_DAILY_DAYS', 30));

        $basePath = storage_path('logs/' . ucfirst($channelName));

        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        $filePath = $basePath . '/' . strtolower($channelName) . '.log';

        $handler = new RotatingFileHandler(
            filename:    $filePath,
            maxFiles:    $maxFiles,
            level:       Logger::toMonologLevel($level),
            filePermission: 0644,
        );

        // El RotatingFileHandler genera archivos con el sufijo de fecha automáticamente.
        // Formato resultante: storage/logs/Auth/auth-YYYY-MM-DD.log
        $handler->setFilenameFormat('{filename}-{date}', 'Y-m-d');

        $logger = new Logger($channelName);
        $logger->pushHandler($handler);

        // Los processors se ejecutan en orden LIFO en Monolog.
        // RequestIdProcessor debe aplicarse antes que SensitiveDataProcessor
        // para que el contexto base también quede enmascarado si contiene datos sensibles.
        $logger->pushProcessor(new RequestIdProcessor($this->logContext));
        $logger->pushProcessor($this->sensitiveProcessor);

        return $logger;
    }
}