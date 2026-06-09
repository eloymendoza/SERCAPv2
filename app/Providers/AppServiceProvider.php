<?php

namespace App\Providers;

use App\Logging\LogContext;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Scoped garantiza un ciclo de vida acotado al request, compatible con Octane.
        $this->app->scoped(LogContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar HTTPS solo en entorno de producción
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Propagación de traza de logs para procesos en segundo plano
        Queue::createPayloadUsing(function () {
            if (app()->bound(LogContext::class)) {
                $context = app(LogContext::class);
                if ($context->isInitialized()) {
                    return [
                        'trace_request_id' => $context->requestId(),
                        'trace_channel'    => $context->channel(),
                    ];
                }
            }
            return [];
        });

        Queue::before(function (JobProcessing $event) {
            $payload = $event->job->payload();
            if (!empty($payload['trace_request_id'])) {
                app(LogContext::class)->initializeAsync(
                    $payload['trace_request_id'],
                    $payload['trace_channel'] ?? 'queue',
                    $event->job->resolveName()
                );
            }
        });
    }
}