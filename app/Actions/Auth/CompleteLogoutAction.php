<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Ejecución de cierre de sesión profundo e invalidación de estados.
 */
class CompleteLogoutAction
{
    /**
     * @param Request $request
     */
    public function execute(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->setUserResolver(fn () => null);

        foreach (array_keys(config('auth.guards')) as $guardName) {
            $guard = Auth::guard($guardName);
            if (method_exists($guard, 'logout')) {
                $guard->logout();
            }
        }

        Auth::forgetUser();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::channel('auth')->info('Sesión invalidada y guards limpiados por CompleteLogoutAction');
    }
}
