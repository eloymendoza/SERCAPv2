<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearUserSessionsAction
{
    /**
     * Elimina todas las sesiones activas del usuario para garantizar sesión única.
     */
    public function execute(int $userId): void
    {
        DB::table('sessions')->where('user_id', $userId)->delete();
        
        Log::channel('auth')->info("Sesiones previas eliminadas para el usuario ID: {$userId}");
    }
}
