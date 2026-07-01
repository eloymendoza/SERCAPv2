<?php

namespace App\Domain\Puestos\Services;

use App\Traits\HandlesProcess;
use App\Domain\Puestos\Models\Puesto;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domain\Requisiciones\Enums\VacanteEstado;

/**
 * Servicio encargado de gestionar la lógica de negocio y consultas complejas
 * relacionadas con la entidad Puesto.
 */
class PuestoService
{
    use HandlesProcess;

    public function __construct()
    {
        // Constructor listo para inyectar Mappers o Repositorios en un futuro
    }

    protected function getLogChannel(): string
    {
        return 'puestos';
    }

    /**
     * Procesa la extracción de puestos en bloque con paginación,
     * priorizando aquellos que requieren vinculación urgente al SGC.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $this->logger()->info("Consultando puestos para vinculación SGC (paginado).");

        return $this->handle(function () use ($perPage) {
            return Puesto::query()
                ->select(['id', 'nombre_puesto', 'tipo'])
                ->with('perfilSgc')
                ->withCount(['detallesRequisicion as urgente' => function($q) {
                    $q->whereHas('vacantes', function($q2) {
                        $q2->where('estado', VacanteEstado::PENDIENTE_VINCULACION_SGC->value);
                    });
                }])
                ->orderByDesc('urgente')
                ->orderBy('nombre_puesto')
                ->paginate($perPage);
        }, 'PuestoService@paginate');
    }

    public function find(Puesto $puesto)
    {
        $this->logger()->info("Consultando puesto con ID: {$puesto->id}");

        return $this->handle(function () use ($puesto) {
            $puesto->load('perfilSgc');
            return $puesto;
        }, 'PuestoService@find');
    }
}