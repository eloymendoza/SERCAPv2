<?php

namespace App\App\Api\Requisiciones\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstadoEnum;

class UnicaEnmiendaEnProcesoRule implements ValidationRule
{
    public function __construct(private readonly ?int $solicitudId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return;
        }

        $existeEnProceso = SolicitudRequisicion::where(function ($query) use ($value) {
                // Verificar si hay enmiendas en proceso
                $query->where('requisicion_padre_id', $value)
                      // O si la solicitud original que dio origen a la requisición sigue en proceso
                      ->orWhereHas('requisicion', function($q) use ($value) {
                          $q->where('id', $value);
                      });
            })
            ->when($this->solicitudId, fn($q) => $q->where('id', '!=', $this->solicitudId))
            ->whereIn('estado', [
                SolicitudRequisicionEstadoEnum::BORRADOR->value,
                SolicitudRequisicionEstadoEnum::EN_PROCESO->value,
                SolicitudRequisicionEstadoEnum::RECHAZADO->value,
            ])
            ->exists();

        if ($existeEnProceso) {
            $fail('La requisición seleccionada ya tiene una solicitud o modificación en curso (o rechazada pendiente de corrección). No puede crear otra hasta que la actual sea terminada o cancelada.');
        }
    }
}