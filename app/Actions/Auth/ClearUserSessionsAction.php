<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Eliminación de sesiones concurrentes por ID de usuario.
 */
class ClearUserSessionsAction
{
    /**
     * @param int $userId
     */
    public function execute(int $userId): void
    {
        DB::table('sessions')->where('user_id', $userId)->delete();
        
        Log::channel('auth')->info("Sesiones previas eliminadas para el usuario ID: {$userId}");
    }
}
