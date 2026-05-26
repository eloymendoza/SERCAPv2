<?php

namespace App\DTOs;

use App\Enums\SolicitudRequisicionEstado;

/**
 * Contenedor de datos inmutable para transferir información de SolicitudRequisicion.
 */
class SolicitudRequisicionDTO
{
    /**
     * Inicializa una nueva instancia de SolicitudRequisicionDTO.
     */
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $folio,
        public readonly ?int $proyectoId,
        public readonly ?int $idInstanciaWorkflow,
        public readonly ?int $solicitanteId,
        public readonly ?int $direccionId,
        public readonly ?int $gerenciaId,
        public readonly ?int $coordinacionId,
        public readonly ?string $observaciones,
        public readonly ?SolicitudRequisicionEstado $estado
    ) {}

    /**
     * Transforma el DTO en un array de datos nativos.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'proyecto_id' => $this->proyectoId,
            'id_instancia_workflow' => $this->idInstanciaWorkflow,
            'solicitante_id' => $this->solicitanteId,
            'direccion_id' => $this->direccionId,
            'gerencia_id' => $this->gerenciaId,
            'coordinacion_id' => $this->coordinacionId,
            'observaciones' => $this->observaciones,
            'estado' => $this->estado?->value,
        ];
    }

    /**
     * Crea una instancia de SolicitudRequisicionDTO desde un array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $estado = null;
        if (isset($data['estado'])) {
            $estadoVal = $data['estado'];
            $estado = $estadoVal instanceof SolicitudRequisicionEstado 
                ? $estadoVal 
                : SolicitudRequisicionEstado::tryFrom($estadoVal);
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            folio: $data['folio'] ?? null,
            proyectoId: isset($data['proyecto_id']) ? (int) $data['proyecto_id'] : null,
            idInstanciaWorkflow: isset($data['id_instancia_workflow']) ? (int) $data['id_instancia_workflow'] : null,
            solicitanteId: isset($data['solicitante_id']) ? (int) $data['solicitante_id'] : null,
            direccionId: isset($data['direccion_id']) ? (int) $data['direccion_id'] : null,
            gerenciaId: isset($data['gerencia_id']) ? (int) $data['gerencia_id'] : null,
            coordinacionId: isset($data['coordinacion_id']) ? (int) $data['coordinacion_id'] : null,
            observaciones: $data['observaciones'] ?? null,
            estado: $estado
        );
    }

    /**
     * Retorna una nueva instancia del DTO con el estado actualizado.
     */
    public function withEstado(SolicitudRequisicionEstado $estado): self
    {
        return new self(
            id: $this->id,
            folio: $this->folio,
            proyectoId: $this->proyectoId,
            idInstanciaWorkflow: $this->idInstanciaWorkflow,
            solicitanteId: $this->solicitanteId,
            direccionId: $this->direccionId,
            gerenciaId: $this->gerenciaId,
            coordinacionId: $this->coordinacionId,
            observaciones: $this->observaciones,
            estado: $estado
        );
    }
}