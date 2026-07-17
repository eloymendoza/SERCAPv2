<?php

namespace App\Domain\Puestos\DTOs;

class PuestoDTO
{
    public function __construct(
        public readonly string $nombrePuesto,
        public readonly int $direccionId,
        public readonly string $tipo,
        public readonly ?int $id = null,
        public readonly ?int $reportaAPuestoId = null,
        public readonly ?int $idDocumento = null,
        public readonly ?array $perfilSgc = null,
        public readonly ?int $urgente = null,
        public readonly ?string $estado = null,
        public readonly ?array $direccion = null
    ) {}

    /**
     * Fabrica una instancia del DTO extrayendo el payload validado.
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            nombrePuesto: $data['nombre_puesto'],
            direccionId: (int) $data['direccion_id'],
            tipo: $data['tipo'],
            reportaAPuestoId: array_key_exists('reporta_a_puesto_id', $data) ? ($data['reporta_a_puesto_id'] !== null ? (int) $data['reporta_a_puesto_id'] : null) : null,
            idDocumento: isset($data['id_documento']) ? (int) $data['id_documento'] : null
        );
    }
}