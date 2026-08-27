<?php

namespace App\Domain\Autenticacion\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Domain\Autenticacion\Mappers\UserMapper;
use App\Infrastructure\Clients\DjangoAuthClient;

/**
 * Registra y configura los servicios del dominio de Autenticación.
 */
class DomainAuthServiceProvider extends ServiceProvider
{
    /**
     * Registra dependencias del dominio si es necesario.
     */
    public function register(): void
    {
        // Registro de dependencias si es necesario
    }

    /**
     * Configura el motor de autenticación nativo.
     * 
     * Define el driver 'django' para que Auth::attempt delegue la resolución
     * de credenciales a nuestro proveedor personalizado en lugar de usar Eloquent.
     */
    public function boot(): void
    {
        Auth::provider('django', function ($app, array $config) {
            return new DjangoUserProvider(
                $app->make(DjangoAuthClient::class),
                $app->make(UserMapper::class)
            );
        });
    }
}