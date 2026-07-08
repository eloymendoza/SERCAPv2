<?php

namespace App\App\Api\Puestos\Controllers;

use Exception;
use App\App\Api\Controller;
use Illuminate\Http\JsonResponse;
use App\Domain\Puestos\Services\PerfilSgcService;

class SgcPerfilController extends Controller
{
    public function __construct(
        private readonly PerfilSgcService $service
    ) {}

    /**
     * Retorna la lista de perfiles de puesto del SGC.
     */
    public function index(): JsonResponse
    {
        try {
            $perfiles = $this->service->getPerfilesActivos();
            
            return response()->json([
                'data' => $perfiles->map->toArray()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}