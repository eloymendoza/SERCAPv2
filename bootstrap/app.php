<?php

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Exceptions\BaseApiException;
use Illuminate\Foundation\Application;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response | JsonResponse $response, Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                // Si ya es una de nuestras excepciones, dejar que ella misma se renderice
                if ($e instanceof BaseApiException) {
                    return $e->render();
                }

                // Estandarizar errores comunes de Laravel para la API
                $statusCode = $response->getStatusCode();
                $errorCode = 'INTERNAL_ERROR';
                $message = $e->getMessage() ?: 'Ocurrió un error en el servidor.';

                if ($e instanceof AuthenticationException) {
                    $statusCode = 401;
                    $errorCode = 'AUTH_REQUIRED';
                    $message = 'No se ha proporcionado una sesión válida.';
                }

                if ($e instanceof AccessDeniedHttpException) {
                    $statusCode = 403;
                    $errorCode = 'ACCESS_DENIED';
                    $message = 'No tienes permisos para realizar esta acción.';
                }

                if ($e instanceof NotFoundHttpException) {
                    $statusCode = 404;
                    $errorCode = 'ROUTE_NOT_FOUND';
                    $message = 'El recurso o ruta solicitada no existe.';
                }

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code'    => $errorCode,
                        'message' => $message,
                        'details' => []
                    ]
                ], $statusCode);
            }

            return $response;
        });
    })->create();
