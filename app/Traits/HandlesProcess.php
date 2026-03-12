<?php

namespace App\Traits;

use App\Exceptions\BaseApiException;
use App\Exceptions\Infrastructure\DatabaseInfrastructureException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ServiceException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

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
            throw $e;
        }
        catch (QueryException $e) {
            throw new DatabaseInfrastructureException(
                message: "Error técnico en base de datos registrado en {$context}",
                previous: $e
            );
        }
        catch (ModelNotFoundException $e) {
            throw new ResourceNotFoundException("Recurso en {$context}");
        }
        catch (Throwable $e) {
            Log::critical("[UNEXPECTED_ERROR] {$context}: {$e->getMessage()}", [
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
