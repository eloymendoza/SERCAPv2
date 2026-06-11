<?php

namespace App\Domain\GestorCV\Mappers;

use App\Domain\GestorCV\DTOs\AspiranteDTO;
use App\Domain\GestorCV\DTOs\CertificadoDTO;
use App\Domain\GestorCV\DTOs\ConocimientoTecnicoDTO;
use App\Domain\GestorCV\DTOs\EducacionDTO;
use App\Domain\GestorCV\DTOs\ExperienciaDTO;
use App\Domain\Requisiciones\Models\Aspirante;

/**
 * Responsable de convertir datos entre capas
 * 
 *  Request (array validado)  →  DTO
 *  DTO                       →  array para insert/update en BD
 *  Modelo Eloquent           →  array de respuesta para el front
 */

class AspiranteMapper
{
    /**
     * Construye un AspiranteDTO desde el array validado del FormRequest.
     *
     * @param array<string, mixed> $data  $request->validated()
     */
    public static function fromRequest(array $data): AspiranteDTO
    {
        return AspiranteDTO::fromArray($data);
    }

    /**
     * Devuelve solo los campos del aspirante (sin relaciones)
     * listos para Eloquent::create() o ::update().
     *
     * @return array<string, mixed>
     */
    public static function toModelArray(AspiranteDTO $dto): array
    {
        return [
            'nombres'          => $dto->nombres,
            'apellido_paterno' => $dto->apellidoPaterno,
            'apellido_materno' => $dto->apellidoMaterno,
            'telefono'         => $dto->telefono,
            'email'            => $dto->email,
            'resumen'          => $dto->resumen,
            'tipo_aspirante'   => $dto->tipoAspirante->value,
            'estado_aspirante' => $dto->estadoAspirante->value,
            // Ubicación: strings recibidos del front; ubicacion_id
            // se resuelve aparte en el Service y se agrega al array antes del insert.
            'codigo_postal'    => $dto->codigoPostal,
            'estado'           => $dto->estado,
            'municipio'        => $dto->municipio,
            'asentamiento'     => $dto->asentamiento,
        ];
    }

    /**
     * Array listo para insert en experiencia_aspirantes.
     *
     * @return array<string, mixed>
     */
    public static function experienciaToModelArray(ExperienciaDTO $dto): array
    {
        return [
            'cargo'             => $dto->cargo,
            'nombre_empresa'    => $dto->nombreEmpresa,
            'trabajo_actual'    => $dto->trabajoActual,
            'fecha_inicio'      => $dto->fechaInicio,
            'fecha_fin'         => $dto->trabajoActual ? null : $dto->fechaFin,
            'responsabilidades' => $dto->responsabilidades,
        ];
    }

    /**
     * Array listo para insert en educacion_aspirantes.
     *
     * @return array<string, mixed>
     */
    public static function educacionToModelArray(EducacionDTO $dto): array
    {
        return [
            'institucion'      => $dto->institucion,
            'nivel_estudio_id' => $dto->nivelEstudioId,
            'titulo'           => $dto->titulo,
            'estado_educacion' => $dto->estadoEducacion,
            'anio_fin'         => $dto->estadoEducacion === 'en_curso' ? null : $dto->anioFin,
        ];
    }

    /**
     * Array listo para insert en certificados_aspirantes.
     *
     * @return array<string, mixed>
     */
    public static function certificadoToModelArray(CertificadoDTO $dto): array
    {
        return [
            'nombre'      => $dto->nombre,
            'institucion' => $dto->institucion,
            'anio_fin'    => $dto->anioFin,
        ];
    }

    /**
     * Array listo para insert en conocimientos_tecnicos_aspirantes.
     *
     * @return array<string, mixed>
     */
    public static function conocimientoToModelArray(ConocimientoTecnicoDTO $dto): array
    {
        return [
            'nombre'    => $dto->nombre,
            'categoria' => $dto->categoria ?? 'sin_clasificar',
        ];
    }

    /**
     * Array con el pivot listo para attach() en la relación idiomas.
     * Devuelve [ idioma_id => ['nivel' => '...'] ] para usar con sync()/attach().
     *
     * @return array<int, array<string, string>>
     */
    public static function idiomasToPivotArray(array $idiomas): array
    {
        $pivot = [];
 
        foreach ($idiomas as $dto) {
            /** @var IdiomaDTO $dto */
            $pivot[$dto->idiomaId] = ['nivel' => $dto->nivel];
        }
 
        return $pivot;
    }

    /**
     * Transforma un modelo Aspirante (con relaciones cargadas) en el
     * array de respuesta que espera el front.
     *
     * Uso: AspiranteMapper::toResponse($aspirante->load([...]))
     *
     * @return array<string, mixed>
     */
    public static function toResponse(Aspirante $aspirante): array
    {
        return [
            // Identificador
            'id' => $aspirante->id,
 
            // Datos personales
            'nombres'         => $aspirante->nombres,
            'apellidoPaterno' => $aspirante->apellido_paterno,
            'apellidoMaterno' => $aspirante->apellido_materno,
            'telefono'        => $aspirante->telefono,
            'email'           => $aspirante->email,
            'resumen'         => $aspirante->resumen,
 
            // Estado y origen
            'tipoAspirante'   => $aspirante->tipo_aspirante,
            'estadoAspirante' => $aspirante->estado_aspirante,
 
            // Ubicación
            'codigoPostal' => $aspirante->codigo_postal,
            'estado'       => $aspirante->estado,
            'municipio'    => $aspirante->municipio,
            'asentamiento' => $aspirante->asentamiento,
 
            // Relaciones
            'experiencias' => $aspirante->relationLoaded('experiencias')
                ? $aspirante->experiencias->map(fn($e) => [
                    'id'               => $e->id,
                    'cargo'            => $e->cargo,
                    'nombreEmpresa'    => $e->nombre_empresa,
                    'trabajoActual'    => (bool) $e->trabajo_actual,
                    'fechaInicio'      => $e->fecha_inicio,
                    'fechaFin'         => $e->fecha_fin,
                    'responsabilidades'=> $e->responsabilidades,
                ])->toArray()
                : [],
 
            'educacion' => $aspirante->relationLoaded('educacion')
                ? $aspirante->educacion->map(fn($e) => [
                    'id'              => $e->id,
                    'institucion'     => $e->institucion,
                    'nivelEstudioId'  => $e->nivel_estudio_id,
                    'nivelEstudio'    => $e->nivelEstudio?->nombre, // nombre legible para el front
                    'titulo'          => $e->titulo,
                    'estadoEducacion' => $e->estado_educacion,
                    'anioFin'         => $e->anio_fin,
                ])->toArray()
                : [],
 
            'certificados' => $aspirante->relationLoaded('certificados')
                ? $aspirante->certificados->map(fn($c) => [
                    'id'          => $c->id,
                    'nombre'      => $c->nombre,
                    'institucion' => $c->institucion,
                    'anioFin'     => $c->anio_fin,
                ])->toArray()
                : [],
 
            'conocimientosTecnicos' => $aspirante->relationLoaded('conocimientosTecnicos')
                ? $aspirante->conocimientosTecnicos->map(fn($c) => [
                    'id'        => $c->id,
                    'nombre'    => $c->nombre,
                    'categoria' => $c->categoria,
                ])->toArray()
                : [],
 
            'idiomas' => $aspirante->relationLoaded('idiomas')
                ? $aspirante->idiomas->map(fn($i) => [
                    'idiomaId' => $i->idioma_id,      // del catálogo
                    'nombre'   => $i->nombre,          // del catálogo
                    'nivel'    => $i->pivot->nivel,    // de la tabla pivote
                ])->toArray()
                : [],
 
            // Auditoría
            'creadoEn'        => $aspirante->created_at?->toIso8601String(),
            'actualizadoEn'   => $aspirante->updated_at?->toIso8601String(),
        ];
    }
}