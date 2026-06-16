<?php

namespace App\Domain\EstructuraOrganizacional\Services;

use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;
use App\Domain\EstructuraOrganizacional\Mappers\UnidadOrganizativaMapper;

class UnidadOrganizativaService
{
    use HandlesProcess;

    public function __construct(
        private readonly UnidadOrganizativaMapper $mapper
    ) {}

    protected function getLogChannel(): string
    {
        return 'estructura_organizacional';
    }

    /**
     * Inicia la transacción para consolidar el nodo organizativo en el árbol.
     */
    public function create(UnidadOrganizativaDTO $dto): UnidadOrganizativaDTO
    {
        $this->logger()->info("Iniciando creación de unidad organizativa.", ['nombre' => $dto->nombre]);

        return $this->handle(function () use ($dto) {
            $createdDto = DB::transaction(function () use ($dto) {
                $data = $this->mapper->toPersistenceArray($dto);
                $model = UnidadOrganizativa::create($data);
                return $this->mapper->toDTO($model);
            });

            $this->logger()->info("Unidad organizativa registrada.", ['id' => $createdDto->id]);
            return $createdDto;
        }, 'UnidadOrganizativaService@create');
    }

    /**
     * Sincroniza la mutación de estado o jerarquía en el registro orgánico.
     */
    public function update(UnidadOrganizativa $model, UnidadOrganizativaDTO $dto): UnidadOrganizativaDTO
    {
        $this->logger()->info("Actualizando atributos jerárquicos.", ['id' => $model->id]);

        return $this->handle(function () use ($model, $dto) {
            $updatedDto = DB::transaction(function () use ($model, $dto) {
                // Evitamos sobrescribir con null si no venía en el request, pero asumimos DTO completo aquí.
                $data = array_filter($this->mapper->toPersistenceArray($dto), function($v) { return !is_null($v); });
                
                // Si explícitamente es un desligamiento de parent_id o encargado:
                if (property_exists($dto, 'parentId') && $dto->parentId === null) $data['parent_id'] = null;
                if (property_exists($dto, 'encargadoId') && $dto->encargadoId === null) $data['encargado_id'] = null;

                $model->update($data);
                return $this->mapper->toDTO($model);
            });

            $this->logger()->info("Unidad organizativa mutada exitosamente.", ['id' => $model->id]);
            return $updatedDto;
        }, 'UnidadOrganizativaService@update');
    }

    /**
     * Resuelve las relaciones inmediatas (responsable y ancestro) del recurso.
     */
    public function find(UnidadOrganizativa $model): UnidadOrganizativaDTO
    {
        return $this->handle(function () use ($model) {
            $model->loadMissing(['parent', 'encargado']);
            return $this->mapper->toDTO($model);
        }, 'UnidadOrganizativaService@find');
    }

    /**
     * Procesa la extracción de nodos organizacionales en bloque con paginación.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->handle(function () use ($perPage) {
            $paginator = UnidadOrganizativa::with('encargado')->paginate($perPage);
            
            $paginator->getCollection()->transform(function ($model) {
                return $this->mapper->toDTO($model);
            });

            return $paginator;
        }, 'UnidadOrganizativaService@paginate');
    }

    /**
     * Declara la baja lógica (SoftDelete) del nodo evitando romper el historial transaccional.
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