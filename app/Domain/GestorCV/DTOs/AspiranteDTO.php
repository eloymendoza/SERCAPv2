<?php

namespace App\Domain\GestorCV\DTOs;

use App\Domain\GestorCV\Enums\EstadoAspirante;
use App\Domain\GestorCV\Enums\TipoAspirante;

/**
 * Contenedor de datos inmutable para transferir informaciónd de un Aspirante.
 * 
 */
class AspiranteDTO
{
    public function __construct(
        // datos personales
        public readonly string $nombres,
        public readonly string $apellidoPaterno,
        public readonly string $apellidoMaterno,
        public readonly string $telefono,
        public readonly string $email,
        public readonly ?string $resumen,
        public readonly TipoAspirante $tipoAspirante, // enum tipo aspirante
        public readonly EstadoAspirante $estadoAspirante, // enum estado aspirante

        // ubicación (strings; ubicacion_id se resuelve en el service)
        public readonly ?string $codigoPostal,
        public readonly ?string $estado,
        public readonly ?string $municipio,
        public readonly ?string $asentamiento,

        // relaciones anidadas
        public readonly array $experiencias,
        public readonly array $educacion,
        public readonly array $certificados,
        public readonly array $conocimientosTecnicos,
        public readonly array $idiomas,
    ) {}

    /**
     * Transforma el DTO en un array de datos nativos (snake_case para la BD).
     */
    public function toArray(): array
    {
        return [
            'nombres'          => $this->nombres,
            'apellido_paterno' => $this->apellidoPaterno,
            'apellido_materno' => $this->apellidoMaterno,
            'telefono'         => $this->telefono,
            'email'            => $this->email,
            'resumen'          => $this->resumen,
            'tipo_aspirante'   => $this->tipoAspirante->value,
            'estado_aspirante' => $this->estadoAspirante->value,
            'codigo_postal'    => $this->codigoPostal, // a partir de este campo, no los tengo en esta tabla, tengo que ver cómo se va a manejar este aspecto
            'estado_geo'       => $this->estado, // y por el momento, así no se llama el campo
            'municipio'        => $this->municipio,
            'asentamiento'     => $this->asentamiento,
        ];
    }

    /**
     * Crea una instancia de AspiranteDTO desde el array validado del Request.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nombres:          $data['nombres'],
            apellidoPaterno:  $data['apellidoPaterno'],
            apellidoMaterno:  $data['apellidoMaterno'],
            telefono:         $data['telefono'],
            email:            $data['email'],
            resumen:          $data['resumen'] ?? null,
            tipoAspirante:    TipoAspirante::from($data['tipoAspirante'] ?? 'nuevo_aspirante'),
            estadoAspirante:  EstadoAspirante::from($data['estadoAspirante'] ?? 'nuevo'),
 
            codigoPostal:  $data['codigoPostal'] ?? null,
            estado:        $data['estado'] ?? null,
            municipio:     $data['municipio'] ?? null,
            asentamiento:  $data['asentamiento'] ?? null,
 
            experiencias: array_map(
                fn(array $e) => ExperienciaDTO::fromArray($e),
                $data['experiencias'] ?? []
            ),
            educacion: array_map(
                fn(array $e) => EducacionDTO::fromArray($e),
                $data['educacion'] ?? []
            ),
            certificados: array_map(
                fn(array $c) => CertificadoDTO::fromArray($c),
                $data['certificados'] ?? []
            ),
            conocimientosTecnicos: array_map(
                fn(array $c) => ConocimientoTecnicoDTO::fromArray($c),
                $data['conocimientosTecnicos'] ?? []
            ),
            idiomas: array_map(
                fn(array $i) => IdiomaDTO::fromArray($i),
                $data['idiomas'] ?? []
            ),
        );
    }

    /**
     * Crea una instancia de AspiranteDTO desde el array validado del Request.
     */
    public function withEstado(EstadoAspirante $estado): self
    {
        return new self(
            nombres:               $this->nombres,
            apellidoPaterno:       $this->apellidoPaterno,
            apellidoMaterno:       $this->apellidoMaterno,
            telefono:              $this->telefono,
            email:                 $this->email,
            resumen:               $this->resumen,
            tipoAspirante:         $this->tipoAspirante,
            estadoAspirante:       $estado,
            codigoPostal:          $this->codigoPostal,
            estado:                $this->estado,
            municipio:             $this->municipio,
            asentamiento:          $this->asentamiento,
            experiencias:          $this->experiencias,
            educacion:             $this->educacion,
            certificados:          $this->certificados,
            conocimientosTecnicos: $this->conocimientosTecnicos,
            idiomas:               $this->idiomas,
        );
    }
}