<?php

namespace App\Traits;

use Throwable;
use Illuminate\Support\Facades\Log;
use App\Exceptions\BaseApiException;
use App\Exceptions\ServiceException;
use Illuminate\Database\QueryException;
use App\Exceptions\ResourceNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\Infrastructure\DatabaseInfrastructureException;

trait HandlesProcess
{
    /**
     * Traduce excepciones técnicas a excepciones de dominio o infraestructura.
     */
    protected function handle(callable $callback, string $context = ''): mixed
    {
        try {
            return $callback();
        } 
        catch (BaseApiException $e) {
            Log::channel('auth')->error("[BASE_API_ERROR] {$context}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
        catch (QueryException $e) {
            Log::channel('auth')->error("[DATABASE_ERROR] {$context}: {$e->getMessage()}", [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new DatabaseInfrastructureException(
                message: "Error técnico en base de datos registrado en {$context}",
                previous: $e
            );
        }
        catch (ModelNotFoundException $e) {
            Log::channel('auth')->error("[MODEL_NOT_FOUND] {$context}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);

            throw new ResourceNotFoundException("Recurso en {$context}");
        }
        catch (Throwable $e) {
            Log::channel('auth')->critical("[UNEXPECTED_ERROR] {$context}: {$e->getMessage()}", [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new ServiceException(
                message: "Error interno del servidor.",
                statusCode: 500,
                errorCode: 'INTERNAL_SERVER_ERROR'
            );
        }
    }
}