<?php

namespace App\Domain\GestorCV\Services;

use App\Domain\GestorCV\DTOs\AspiranteDTO;
use App\Domain\GestorCV\Mappers\AspiranteMapper;
use App\Domain\Requisiciones\Models\Aspirante;
use Illuminate\Support\Facades\DB;

class AspiranteService
{
    // Crear aspirante
    /**
     * Persiste un nuevo aspirante con todas sus relaciones en una
     * única transacción. Si cualquier paso falla, se revierte todo.
     */
    public function store(AspiranteDTO $dto): Aspirante
    {
        return DB::transaction(function () use ($dto) {
            $aspirante = Aspirante::create(
                $this->resolveModelArray($dto)
            );
            
            // persistir relaciones hasMany
            $this->syncExperiencias($aspirante, $dto);
            $this->syncEducacion($aspirante, $dto);
            $this->syncCertificados($aspirante, $dto);
            $this->syncConocimientosTecnicos($aspirante, $dto);

            // persistir relación N:M (idiomas con pivot)
            $this->syncIdiomas($aspirante, $dto);

            // retornar el modelo con todas las relaciones cargadas
            // para que el controlador pueda pasarlo directo al Mapper
            return $aspirante->load([
                'experiencias',
                'educacion.nivelEstudio',
                'certificados',
                'conocimientosTecnicos',
                'idiomas',
            ]);
        });
    }

    /**
     * Construye el array del aspirante resolviendo ubicacion_id si aplica.
     * Cuando la integración con la BD de ubicaciones esté lista, este es
     * el único lugar donde se conecta — el resto del flujo no cambia.
     *
     * @return array<string, mixed>
     */
    private function resolveModelArray(AspiranteDTO $dto): array
    {
        $data = AspiranteMapper::toModelArray($dto);
        // TODO: resolver ubicacion_id contra la BD de ubicaciones
        // cuando el servicio externo esté disponible.
        // Ejemplo:
        // $data['ubicacion_id'] = $this->ubicacionService->resolveId(
        //     $dto->codigoPostal,
        //     $dto->estado,
        //     $dto->municipio,
        //     $dto->asentamiento,
        // );
 
        return $data;
    }

    /**
     * Inserta las experiencias laborales del aspirante.
     * createMany() hace un insert por cada elemento pero dentro de la misma transacción.
     */
    private function syncExperiencias(Aspirante $aspirante, AspiranteDTO $dto): void
    {
        if (empty($dto->experiencias)) {
            return;
        }
 
        $aspirante->experiencias()->createMany(
            array_map(
                fn($exp) => AspiranteMapper::experienciaToModelArray($exp),
                $dto->experiencias
            )
        );
    }

     /**
     * Inserta los registros de educación del aspirante.
     */
    private function syncEducacion(Aspirante $aspirante, AspiranteDTO $dto): void
    {
        if (empty($dto->educacion)) {
            return;
        }
 
        $aspirante->educacion()->createMany(
            array_map(
                fn($edu) => AspiranteMapper::educacionToModelArray($edu),
                $dto->educacion
            )
        );
    }

    /**
     * Inserta los certificados del aspirante.
     */
    private function syncCertificados(Aspirante $aspirante, AspiranteDTO $dto): void
    {
        if (empty($dto->certificados)) {
            return;
        }
 
        $aspirante->certificados()->createMany(
            array_map(
                fn($cert) => AspiranteMapper::certificadoToModelArray($cert),
                $dto->certificados
            )
        );
    }

    /**
     * Inserta los conocimientos técnicos del aspirante.
     */
    private function syncConocimientosTecnicos(Aspirante $aspirante, AspiranteDTO $dto): void
    {
        if (empty($dto->conocimientosTecnicos)) {
            return;
        }
 
        $aspirante->conocimientosTecnicos()->createMany(
            array_map(
                fn($con) => AspiranteMapper::conocimientoToModelArray($con),
                $dto->conocimientosTecnicos
            )
        );
    }

    /**
     * Sincroniza los idiomas del aspirante en la tabla pivote.
     * sync() en store() es equivalente a attach() pero idempotente —
     * útil si en el futuro reutilizas este método en un update.
     */
    private function syncIdiomas(Aspirante $aspirante, AspiranteDTO $dto): void
    {
        if (empty($dto->idiomas)) {
            return;
        }
 
        $aspirante->idiomas()->sync(
            AspiranteMapper::idiomasToPivotArray($dto->idiomas)
        );
    }

    // función tentativa cuando se llegue a requerir validar si el aspirante tiene un trabajo actual, entonces no aceptar fecha fin
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->experiencias ?? [] as $index => $exp) {
                if (($exp['trabajoActual'] ?? false) && !empty($exp['fechaFin'])) {
                    $validator->errors()->add(
                        "experiencias.$index.fechaFin",
                        'Si es el trabajo actual, no debe tener fecha de fin.'
                    );
                }
            }
        });
    }
}