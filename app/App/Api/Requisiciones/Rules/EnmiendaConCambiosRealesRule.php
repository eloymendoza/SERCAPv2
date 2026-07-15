<?php

namespace App\App\Api\Requisiciones\Rules;

use Closure;
use App\Domain\Requisiciones\Models\Requisicion;
use Illuminate\Contracts\Validation\ValidationRule;

class EnmiendaConCambiosRealesRule implements ValidationRule
{
    public function __construct(private readonly ?int $requisicionPadreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Si no es una enmienda, esta regla no aplica
        if (!$this->requisicionPadreId) {
            return;
        }

        $requisicionPadre = Requisicion::with('detalles')->find($this->requisicionPadreId);
        if (!$requisicionPadre) {
            return; // Si no existe, se encargará otra regla o fallará más adelante
        }

        // $value es el array de detalles que viene en requisicion.detalle
        if (!is_array($value) || empty($value)) {
            return;
        }

        $detallesActuales = $requisicionPadre->detalles->keyBy('puesto_id');
        $huboCambio = false;

        foreach ($value as $detallePayload) {
            $puestoId = $detallePayload['puesto_id'] ?? null;
            
            // Puestos nuevos (propuestos o existentes que no estaban en la requisición) son cambios.
            if (!$puestoId || !$detallesActuales->has($puestoId)) {
                $huboCambio = true;
                break;
            }

            $detalleOriginal = $detallesActuales->get($puestoId);
            $nuevaCantidad = (int) ($detallePayload['cantidad_solicitada'] ?? 0);
            $nuevoSueldo = (float) ($detallePayload['sueldo_asignado'] ?? 0);

            if ($nuevaCantidad !== (int) $detalleOriginal->cantidad_solicitada) {
                $huboCambio = true;
                break;
            }

            if ($nuevoSueldo !== (float) $detalleOriginal->sueldo_asignado) {
                $huboCambio = true;
                break;
            }
        }

        // Si la cantidad de puestos en el array difiere de la original, hubo cambios estructurales
        if (count($value) !== $detallesActuales->count()) {
            $huboCambio = true;
        }

        if (!$huboCambio) {
            $fail('La solicitud de enmienda no contiene modificaciones reales en las cantidades o sueldos respecto a la requisición activa original.');
        }
    }
}