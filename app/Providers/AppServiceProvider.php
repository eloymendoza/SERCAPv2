<?php

namespace App\Providers;

use App\Logging\LogContext;
use Illuminate\Support\Facades\URL;
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
    }
}