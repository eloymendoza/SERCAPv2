<?php

namespace App\Domain\EstructuraOrganizacional\Services;

use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;
use App\Domain\EstructuraOrganizacional\Mappers\UnidadOrganizativaMapper;
use App\Domain\EstructuraOrganizacional\Enums\UnidadOrganizativaEstadoEnum;
use App\Domain\EstructuraOrganizacional\Rules\Update\ValidarEstadoLegadoRule;
use App\Domain\EstructuraOrganizacional\Rules\Update\BloquearEdicionEstructuralRule;
use App\Domain\EstructuraOrganizacional\Rules\Shared\ValidarExclusividadEncargadoRule;
use App\Domain\EstructuraOrganizacional\Rules\Activate\ValidarRequisitosActivacionRule;

class UnidadOrganizativaService
{
    use HandlesProcess;

    public function __construct(
        private readonly UnidadOrganizativaMapper $mapper,
        private readonly ValidarEstadoLegadoRule $validarEstadoLegadoRule,
        private readonly BloquearEdicionEstructuralRule $bloquearEdicionEstructuralRule,
        private readonly ValidarRequisitosActivacionRule $validarRequisitosActivacionRule,
        private readonly ValidarExclusividadEncargadoRule $validarExclusividadEncargadoRule
    ) {}

    protected function getLogChannel(): string
    {
        return 'estructura_organizacional';
    }

    /**
     * Crea una nueva unidad organizativa en la base de datos.
     */
    public function create(UnidadOrganizativaDTO $dto): UnidadOrganizativaDTO
    {
        $this->logger()->info("Iniciando creación de unidad organizativa.", ['nombre' => $dto->nombre]);

        return $this->handle(function () use ($dto) {
            $createdDto = DB::transaction(function () use ($dto) {
                $data = $this->mapper->toPersistenceArray($dto);
                
                // Forzar regla de negocio: Toda unidad nueva nace como borrador.
                $data['estado'] = UnidadOrganizativaEstadoEnum::BORRADOR->value;
                $data['enabled_at'] = null; // No puede tener vigencia hasta ser activada

                $model = UnidadOrganizativa::create($data);
                return $this->mapper->toDTO($model);
            });

            $this->logger()->info("Unidad organizativa registrada.", ['id' => $createdDto->id]);
            return $createdDto;
        }, 'UnidadOrganizativaService@create');
    }

    /**
     * Actualiza los datos de una unidad organizativa.
     */
    public function update(UnidadOrganizativa $model, UnidadOrganizativaDTO $dto): UnidadOrganizativaDTO
    {
        $this->logger()->info("Actualizando atributos jerárquicos.", ['id' => $model->id]);

        return $this->handle(function () use ($model, $dto) {
            $rules = [
                $this->validarEstadoLegadoRule,
                $this->bloquearEdicionEstructuralRule,
                $this->validarExclusividadEncargadoRule
            ];
            
            foreach ($rules as $rule) {
                $rule->validate($model, $dto);
            }

            $updatedDto = DB::transaction(function () use ($model, $dto) {
                $data = array_filter($this->mapper->toPersistenceArray($dto), function($v) { return !is_null($v); });
                
                if (property_exists($dto, 'parentId') && $dto->parentId === null) $data['parent_id'] = null;
                if (property_exists($dto, 'encargadoId') && $dto->encargadoId === null) $data['encargado_id'] = null;
                if (property_exists($dto, 'encargadoUsuario') && $dto->encargadoUsuario === null) $data['encargado_usuario'] = null;

                $model->update($data);
                return $this->mapper->toDTO($model);
            });

            $this->logger()->info("Unidad organizativa mutada exitosamente.", ['id' => $model->id]);
            return $updatedDto;
        }, 'UnidadOrganizativaService@update');
    }

    /**
     * Activa una unidad organizativa y cierra el ciclo de vida de su predecesora (si aplica).
     */
    public function activate(UnidadOrganizativa $model): void
    {
        $this->logger()->info("Iniciando activación de unidad organizativa.", ['id' => $model->id]);

        $this->handle(function () use ($model) {
            $rules = [
                $this->validarRequisitosActivacionRule,
                $this->validarExclusividadEncargadoRule
            ];
            
            foreach ($rules as $rule) {
                $rule->validate($model);
            }

            DB::transaction(function () use ($model) {
                $model->update([
                    'estado' => UnidadOrganizativaEstadoEnum::ACTIVO->value,
                    'enabled_at' => now(),
                ]);

                if ($model->reemplaza_a_id) {
                    $predecesora = $model->reemplazaA;
                    if ($predecesora) {
                        $predecesora->update([
                            'estado' => UnidadOrganizativaEstadoEnum::INACTIVO->value,
                            'disabled_at' => now(),
                        ]);
                        $this->logger()->info("Predecesora desactivada automáticamente.", ['id' => $predecesora->id]);
                    }
                }
            });
            $this->logger()->info("Unidad organizativa activada exitosamente.", ['id' => $model->id]);
        }, 'UnidadOrganizativaService@activate');
    }

    /**
     * Desactiva una unidad organizativa cerrando su vigencia de forma manual.
     */
    public function deactivate(UnidadOrganizativa $model): void
    {
        $this->logger()->info("Iniciando desactivación de unidad organizativa.", ['id' => $model->id]);

        $this->handle(function () use ($model) {
            DB::transaction(function () use ($model) {
                $model->update([
                    'estado' => UnidadOrganizativaEstadoEnum::INACTIVO->value,
                    'disabled_at' => now(),
                ]);
            });
            $this->logger()->info("Unidad organizativa desactivada.", ['id' => $model->id]);
        }, 'UnidadOrganizativaService@deactivate');
    }

    /**
     * Obtiene una unidad organizativa junto con su jefe y su área padre.
     */
    public function find(UnidadOrganizativa $model): UnidadOrganizativaDTO
    {
        return $this->handle(function () use ($model) {
            $model->loadMissing(['parent', 'encargado']);
            return $this->mapper->toDTO($model);
        }, 'UnidadOrganizativaService@find');
    }

    /**
     * Obtiene una lista paginada de las unidades organizativas activas.
     */
    public function paginate(int $perPage = 15, ?string $nivel = null): LengthAwarePaginator
    {
        return $this->handle(function () use ($perPage, $nivel) {
            $paginator = UnidadOrganizativa::with('encargado')
                ->where('estado', UnidadOrganizativaEstadoEnum::ACTIVO->value)
                ->when($nivel, fn($q) => $q->where('nivel', $nivel))
                ->paginate($perPage);
            
            $paginator->getCollection()->transform(function ($model) {
                return $this->mapper->toDTO($model);
            });

            return $paginator;
        }, 'UnidadOrganizativaService@paginate');
    }

    /**
     * Elimina lógicamente una unidad organizativa sin borrarla físicamente.
     */
    public function delete(UnidadOrganizativa $model): void
    {
        $this->logger()->info("Iniciando baja de registro organizacional.", ['id' => $model->id]);

        $this->handle(function () use ($model) {
            DB::transaction(function () use ($model) {
                $model->delete();
            });
            $this->logger()->info("Registro dado de baja lógica.", ['id' => $model->id]);
        }, 'UnidadOrganizativaService@delete');
    }
}