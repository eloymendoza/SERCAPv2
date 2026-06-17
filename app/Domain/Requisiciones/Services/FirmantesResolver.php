<?php

namespace App\Domain\Requisiciones\Services;

use App\Domain\Autenticacion\Models\User;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

/**
 * Resuelve los firmantes de una solicitud de requisición según reglas de negocio.
 *
 * Las reglas dependen del rol del elaborador y del total de vacantes de la requisición.
 * GTCO se resuelve desde el encargado de la UO configurada en services.workflow.gtco_unidad_id.
 */
class FirmantesResolver
{
    private readonly int $gtcoUnidadId;
    private readonly int $workflowId;

    public function __construct()
    {
        $this->gtcoUnidadId = (int) config('services.workflow.gtco_unidad_id');
        $this->workflowId = (int) config('services.workflow.requisicion_workflow_id');
    }

    /**
     * Resuelve los firmantes para una solicitud de requisición.
     *
     * @return array{requiere_workflow: bool, workflow_id: int, firmantes: array<int, array{Id_personal: int, orden: int}>}
     */
    public function resolverParaRequisicion(User $elaborador, SolicitudRequisicion $solicitud): array
    {
        $rol = $this->determinarRol($elaborador);
        $totalVacantes = $this->calcularTotalVacantes($solicitud);
        $firmantes = [];

        match ($rol) {
            'director' => $firmantes = $this->resolverFirmantesDirector($totalVacantes),
            'gerente_proyecto', 'jefe_proyecto', 'eap' => $firmantes = $this->resolverFirmantesEstandar($solicitud, $totalVacantes),
        };

        return [
            'requiere_workflow' => count($firmantes) > 0,
            'workflow_id' => $this->workflowId,
            'firmantes' => $firmantes,
        ];
    }

    /**
     * Determina el rol operativo del elaborador en orden de prioridad.
     *
     * Director tiene prioridad sobre cualquier otro rol para garantizar auto-aprobación.
     */
    private function determinarRol(User $elaborador): string
    {
        if ($elaborador->isDirector()) {
            return 'director';
        }

        if ($elaborador->isGerenteProyecto()) {
            return 'gerente_proyecto';
        }

        if ($elaborador->isJefeProyecto()) {
            return 'jefe_proyecto';
        }

        return 'eap';
    }

    /**
     * Resuelve firmantes cuando el elaborador es Director.
     *
     * Solo requiere GTCO si el total de vacantes supera 10.
     * @return array<int, array{Id_personal: int, orden: int}>
     */
    private function resolverFirmantesDirector(int $totalVacantes): array
    {
        if ($totalVacantes <= 10) {
            return [];
        }

        return [
            ['Id_personal' => $this->resolverGTCO(), 'orden' => 1],
        ];
    }

    /**
     * Resuelve firmantes por defecto (JP, GP, EAP).
     *
     * Firmante 1: Director del área. Firmante 2: GTCO (si aplica).
     * @return array<int, array{Id_personal: int, orden: int}>
     */
    private function resolverFirmantesEstandar(SolicitudRequisicion $solicitud, int $totalVacantes): array
    {
        $directorId = $this->resolverDirectorArea($solicitud->direccion_id);

        $firmantes = [
            ['Id_personal' => $directorId, 'orden' => 1],
        ];

        if ($totalVacantes > 10) {
            $firmantes[] = ['Id_personal' => $this->resolverGTCO(), 'orden' => 2];
        }

        return $firmantes;
    }

    /**
     * Calcula el total de vacantes sumando cantidad_solicitada de todos los detalles.
     */
    private function calcularTotalVacantes(SolicitudRequisicion $solicitud): int
    {
        return (int) $solicitud->requisicion
            ?->detalles()
            ->sum('cantidad_solicitada') ?? 0;
    }

    /**
     * Resuelve el personal_id del encargado de GTCO desde la UO configurada.
     */
    private function resolverGTCO(): int
    {
        return (int) UnidadOrganizativa::findOrFail($this->gtcoUnidadId)->encargado_id;
    }

    /**
     * Resuelve el personal_id del Director del área desde la UO de dirección.
     */
    private function resolverDirectorArea(int $direccionId): int
    {
        return (int) UnidadOrganizativa::findOrFail($direccionId)->encargado_id;
    }
}