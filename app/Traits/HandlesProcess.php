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
     * Define el canal de logs a utilizar.
     */
    abstract protected function getLogChannel(): string;

    /**
     * Traduce excepciones técnicas a excepciones de dominio o infraestructura.
     */
    protected function handle(callable $callback, string $context = ''): mixed
    {
        $channel = $this->getLogChannel();

        try {
            return $callback();
        } 
        catch (BaseApiException $e) {
            Log::channel($channel)->error("[BASE_API_ERROR] {$context}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
        catch (QueryException $e) {
            Log::channel($channel)->error("[DATABASE_ERROR] {$context}: {$e->getMessage()}", [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new DatabaseInfrastructureException(
                message: "Ocurrió un error inesperado al procesar la información en la base de datos.",
                previous: $e
            );
        }
        catch (ModelNotFoundException $e) {
            Log::channel($channel)->error("[MODEL_NOT_FOUND] {$context}: {$e->getMessage()}", [
                'model' => $e->getModel(),
                'ids'   => $e->getIds(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new ResourceNotFoundException("registro");
        }
        catch (Throwable $e) {
            Log::channel($channel)->critical("[UNEXPECTED_ERROR] {$context}: {$e->getMessage()}", [
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