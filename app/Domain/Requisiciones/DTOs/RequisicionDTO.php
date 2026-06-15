<?php

namespace App\Domain\Requisiciones\DTOs;

use App\Domain\Requisiciones\Enums\RequisicionEstado;

/**
 * Contenedor de datos inmutable para transferir información de la Requisicion.
 * 
 * @property \App\Domain\Requisiciones\DTOs\DetalleRequisicionDTO[]|null $detalles
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
        /** @var array<\App\Domain\Requisiciones\DTOs\DetalleRequisicionDTO>|null */
        public readonly ?array $detalles
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
            'detalles' => $this->detalles ? array_map(fn($d) => $d->toArray(), $this->detalles) : null,
            'total_vacantes' => $this->getTotalVacantes(),
        ];
    }

    /**
     * Calcula dinámicamente el total de vacantes sumando las cantidades solicitadas en cada detalle.
     */
    public function getTotalVacantes(): int
    {
        if (empty($this->detalles)) return 0;
        
        return array_reduce($this->detalles, fn($carry, $item) => $carry + ($item->cantidadSolicitada ?? 0), 0);
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
            detalles: isset($data['detalle']) && is_array($data['detalle']) 
                ? array_map(fn($d) => DetalleRequisicionDTO::fromArray($d), $data['detalle']) 
                : null
        );
    }
}