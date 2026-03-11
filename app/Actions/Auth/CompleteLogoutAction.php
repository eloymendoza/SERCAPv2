<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompleteLogoutAction
{
    /**
     * Realiza un cierre de sesión profundo e invalidación de sesión completa.
     */
    public function execute(Request $request): void
    {
        // 1. Logout del guard web
        Auth::guard('web')->logout();

        // 2. Limpiar el usuario del Resolver del Request
        $request->setUserResolver(fn () => null);

        // 3. Limpieza de todos los guards cargados
        foreach (array_keys(config('auth.guards')) as $guardName) {
            $guard = Auth::guard($guardName);
            if (method_exists($guard, 'logout')) {
                $guard->logout();
            }
        }

        // 4. Forzar olvido del usuario
        Auth::forgetUser();

        // 5. Invalidar y regenerar token de sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::channel('auth')->info('Sesión invalidada y guards limpiados por CompleteLogoutAction');
    }
}
