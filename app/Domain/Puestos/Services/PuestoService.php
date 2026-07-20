<?php

namespace App\Domain\Puestos\Services;

use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\Auth;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\DTOs\PuestoDTO;
use App\Domain\Puestos\Mappers\PuestoMapper;
use App\Domain\Puestos\Enums\PuestoEstadoEnum;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domain\Requisiciones\Enums\VacanteEstadoEnum;
use App\Domain\Puestos\Actions\VincularPerfilSgcAction;
use App\Domain\Puestos\Events\PuestoCreadoSinPerfil;
use App\Domain\Puestos\Rules\Update\ValidarEstadoLegadoRule;
use App\Domain\Puestos\Rules\Update\ValidarCambioPerfilSlaRule;
use App\Domain\Puestos\Rules\Delete\ValidarRequisicionesActivasRule;
use App\Domain\Puestos\Rules\Delete\ValidarPuestosSubordinadosRule;

/**
 * Servicio encargado de gestionar la lógica de negocio y consultas complejas
 * relacionadas con la entidad Puesto.
 */
class PuestoService
{
    use HandlesProcess;

    public function __construct(
        private readonly PuestoMapper $mapper,
        private readonly VincularPerfilSgcAction $vincularAction,
        private readonly ValidarCambioPerfilSlaRule $validarCambioPerfilSlaRule,
        private readonly ValidarEstadoLegadoRule $validarEstadoLegadoRule,
        private readonly ValidarRequisicionesActivasRule $validarRequisicionesActivasRule,
        private readonly ValidarPuestosSubordinadosRule $validarPuestosSubordinadosRule
    ) {
        // Constructor con dependencias inyectadas
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
            $paginator = Puesto::query()
                ->select(['id', 'nombre_puesto', 'tipo', 'direccion_id', 'reporta_a_puesto_id'])
                ->with(['perfilSgc', 'direccion'])
                ->withCount(['detallesRequisicion as urgente' => function($q) {
                    $q->whereHas('vacantes', function($q2) {
                        $q2->where('estado', VacanteEstadoEnum::PENDIENTE_VINCULACION_SGC->value);
                    });
                }])
                ->orderByDesc('urgente')
                ->where('estado', '!=', PuestoEstadoEnum::LEGADO->value)
                ->orderBy('nombre_puesto')
                ->paginate($perPage);
            
            // Transformar la colección a DTOs
            $paginator->getCollection()->transform(function ($puesto) {
                return $this->mapper->toDTO($puesto);
            });

            return $paginator;
        }, 'PuestoService@paginate');
    }

    public function find(Puesto $puesto): PuestoDTO
    {
        $this->logger()->info("Consultando puesto con ID: {$puesto->id}");

        return $this->handle(function () use ($puesto) {
            $this->loadRequiredRelations($puesto);
            return $this->mapper->toDTO($puesto);
        }, 'PuestoService@find');
    }

    public function update(Puesto $puesto, PuestoDTO $dto): PuestoDTO
    {
        $this->logger()->info("Iniciando actualización de puesto.", [
            'id' => $puesto->id,
            'dto' => $dto
        ]);

        return $this->handle(function () use ($puesto, $dto) {
            $rules = [
                $this->validarEstadoLegadoRule,
                $this->validarCambioPerfilSlaRule,
            ];
            
            foreach ($rules as $rule) {
                $rule->validate($puesto, $dto);
            }

            $data = $this->mapper->toPersistenceArray($dto);
            
            if ($dto->idDocumento || $puesto->tienePerfilVinculadoSGC()) {
                $data['estado'] = PuestoEstadoEnum::ACTIVO->value;
            } else {
                $data['estado'] = $puesto->estado === PuestoEstadoEnum::INACTIVO->value ? PuestoEstadoEnum::INACTIVO->value : PuestoEstadoEnum::BORRADOR->value;
            }

            $puesto->update($data);

            if ($dto->idDocumento) {
                if (!$puesto->tienePerfilVinculadoSGC()) {
                    $this->vincularAction->execute($puesto, $dto->idDocumento);
                } else {
                    $perfilActivo = $puesto->perfilSgc;
                    if ($perfilActivo && $perfilActivo->id_documento != $dto->idDocumento) {
                        $perfilActivo->update(['id_documento' => $dto->idDocumento]);
                    }
                }
            }

            $this->logger()->info("Puesto actualizado.", ['id' => $puesto->id]);

            $puesto->refresh();
            $this->loadRequiredRelations($puesto);
            
            return $this->mapper->toDTO($puesto);
        }, 'PuestoService@update');
    }

    public function create(PuestoDTO $dto): PuestoDTO
    {
        $this->logger()->info("Iniciando creación de puesto.", ['dto' => $dto]);

        return $this->handle(function () use ($dto) {
            $data = $this->mapper->toPersistenceArray($dto);
            
            $data['estado'] = $dto->idDocumento ? PuestoEstadoEnum::ACTIVO->value : PuestoEstadoEnum::BORRADOR->value;
            
            $puesto = Puesto::create($data);

            if ($dto->idDocumento) {
                $this->vincularAction->execute($puesto, $dto->idDocumento);
            } else {
                PuestoCreadoSinPerfil::dispatch($puesto, Auth::user()?->id_personal);
            }

            $this->logger()->info("Puesto creado.", ['id' => $puesto->id]);
            
            $this->loadRequiredRelations($puesto);
            return $this->mapper->toDTO($puesto);
        }, 'PuestoService@create');
    }

    public function delete(Puesto $puesto): bool
    {
        $this->logger()->info("Iniciando eliminación de puesto.", ['id' => $puesto->id]);

        return $this->handle(function () use ($puesto) {
            $rules = [$this->validarRequisicionesActivasRule, $this->validarPuestosSubordinadosRule];
            
            foreach ($rules as $rule) {
                $rule->validate($puesto);
            }

            $result = $puesto->delete();
            $this->logger()->info("Puesto eliminado lógicamente.", ['id' => $puesto->id]);
            return $result;
        }, 'PuestoService@delete');
    }

    /**
     * Carga las relaciones y métricas requeridas para transformar el modelo a DTO.
     */
    private function loadRequiredRelations(Puesto $puesto): void
    {
        $puesto->load(['perfilSgc', 'direccion']);
        $puesto->loadCount(['detallesRequisicion as urgente' => function($q) {
            $q->whereHas('vacantes', function($q2) {
                $q2->where('estado', VacanteEstadoEnum::PENDIENTE_VINCULACION_SGC->value);
            });
        }]);
    }
}