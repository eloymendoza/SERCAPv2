<?php

namespace App\Domain\Requisiciones\DTOs;

use App\Domain\Requisiciones\Enums\RequisicionEstado;

/**
 * Contenedor de datos inmutable para transferir información de la Requisicion.
 */
class RequisicionDTO
{
    /**
     * Inicializa una nueva instancia de RequisicionDTO.
     */
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $solicitudId,
        public readonly ?string $folio,
        public readonly int $tipo,
        public readonly ?string $observaciones,
        public readonly ?RequisicionEstado $estado,
        public readonly ?DetalleRequisicionDTO $detalle
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
            'solicitud_id' => $this->solicitudId,
            'folio' => $this->folio,
            'tipo' => $this->tipo,
            'observaciones' => $this->observaciones,
            'estado' => $this->estado?->value,
            'detalle' => $this->detalle?->toArray(),
        ];
    }

    /**
     * Crea una instancia de RequisicionDTO desde un array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $estado = null;
        if (isset($data['estado'])) {
            $estadoVal = $data['estado'];
            $estado = $estadoVal instanceof RequisicionEstado 
                ? $estadoVal 
                : RequisicionEstado::tryFrom($estadoVal);
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            solicitudId: isset($data['solicitud_id']) ? (int) $data['solicitud_id'] : null,
            folio: $data['folio'] ?? null,
            tipo: (int) ($data['tipo'] ?? 1),
            observaciones: $data['observaciones'] ?? null,
            estado: $estado,
            detalle: isset($data['detalle']) ? DetalleRequisicionDTO::fromArray($data['detalle']) : null
        );
    }
}
