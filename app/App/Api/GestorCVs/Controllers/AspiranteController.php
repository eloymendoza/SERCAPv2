<?php

namespace App\App\Api\GestorCVs\Controllers;

use App\App\Api\Controller;
use App\App\Api\Requests\AspiranteRequest;
use App\Domain\GestorCV\Mappers\AspiranteMapper;
use App\Domain\GestorCV\Services\AspiranteService;
use App\Domain\Requisiciones\Models\Aspirante;
use Illuminate\Http\JsonResponse;

class AspiranteController extends Controller
{
    public function __construct(
        private readonly AspiranteService $service
    ) {}

    /**
     * Crea un nuevo aspirante con toda su información del CV.
     */
    public function store(AspiranteRequest $request): JsonResponse
    {
        $dto       = AspiranteMapper::fromRequest($request->validated());
        $aspirante = $this->service->store($dto);
 
        return response()->json([
            'message' => 'Aspirante registrado correctamente.',
            'data'    => AspiranteMapper::toResponse($aspirante),
        ], 201);
    }

     /**
     * Retorna la información completa de un aspirante con sus relaciones.
     */
    public function show(Aspirante $aspirante): JsonResponse
    {
        $aspirante->load([
            'experiencias',
            'educacion.nivelEstudio',
            'certificados',
            'conocimientosTecnicos',
            'idiomas',
        ]);
 
        return response()->json([
            'data' => AspiranteMapper::toResponse($aspirante),
        ]);
    }
}