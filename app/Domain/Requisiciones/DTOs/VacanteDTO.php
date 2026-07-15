<?php

namespace App\Domain\Requisiciones\DTOs;

use App\Domain\Requisiciones\Enums\VacanteEstado;

/**
 * Contenedor de datos inmutable para transferir información de Vacante.
 */
class VacanteDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $detalleRequisicionId,
        public readonly ?VacanteEstado $estado
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'detalle_requisicion_id' => $this->detalleRequisicionId,
            'estado' => $this->estado ? [
                'value' => $this->estado->value,
                'label' => $this->estado->label(),
                'color' => $this->estado->color(),
            ] : null,
        ];
    }

    public static function fromArray(array $data): self
    {
        $estado = null;
        if (isset($data['estado'])) {
            $estadoVal = $data['estado'];
            if (is_array($estadoVal) && isset($estadoVal['value'])) {
                $estadoVal = $estadoVal['value'];
            }
            $estado = $estadoVal instanceof VacanteEstado 
                ? $estadoVal 
                : VacanteEstado::tryFrom($estadoVal);
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            detalleRequisicionId: isset($data['detalle_requisicion_id']) ? (int) $data['detalle_requisicion_id'] : null,
            estado: $estado
        );
    }
}