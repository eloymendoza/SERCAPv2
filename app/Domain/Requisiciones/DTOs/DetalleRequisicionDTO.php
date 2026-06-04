<?php

namespace App\Domain\Requisiciones\DTOs;

/**
 * Contenedor de datos inmutable para transferir información del DetalleRequisicion.
 */
class DetalleRequisicionDTO
{
    /**
     * Inicializa una nueva instancia de DetalleRequisicionDTO.
     */
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $requisicionId,
        public readonly int $puestoId,
        public readonly int $cantidadSolicitada,
        public readonly int $disciplinaId,
        public readonly string $tipoContrato,
        public readonly float $tabuladorSueldo,
        public readonly string $turnoHoras,
        public readonly string $fechaInicio,
        public readonly ?string $fechaTermino,
        public readonly string $fechaLimiteRequerimiento,
        public readonly ?array $empleadosPropuestos
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
            'requisicion_id' => $this->requisicionId,
            'puesto_id' => $this->puestoId,
            'cantidad_solicitada' => $this->cantidadSolicitada,
            'disciplina_id' => $this->disciplinaId,
            'tipo_contrato' => $this->tipoContrato,
            'tabulador_sueldo' => $this->tabuladorSueldo,
            'turno_horas' => $this->turnoHoras,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_termino' => $this->fechaTermino,
            'fecha_limite_requerimiento' => $this->fechaLimiteRequerimiento,
            'empleados_propuestos' => $this->empleadosPropuestos,
        ];
    }

    /**
     * Crea una instancia de DetalleRequisicionDTO desde un array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            requisicionId: isset($data['requisicion_id']) ? (int) $data['requisicion_id'] : null,
            puestoId: (int) $data['puesto_id'],
            cantidadSolicitada: (int) $data['cantidad_solicitada'],
            disciplinaId: (int) $data['disciplina_id'],
            tipoContrato: (string) $data['tipo_contrato'],
            tabuladorSueldo: (float) $data['tabulador_sueldo'],
            turnoHoras: (string) $data['turno_horas'],
            fechaInicio: (string) $data['fecha_inicio'],
            fechaTermino: $data['fecha_termino'] ?? null,
            fechaLimiteRequerimiento: (string) $data['fecha_limite_requerimiento'],
            empleadosPropuestos: $data['empleados_propuestos'] ?? null
        );
    }
}
