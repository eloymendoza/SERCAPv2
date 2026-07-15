<?php

namespace App\App\Api\Requisiciones\Rules;

use Closure;
use App\Domain\Requisiciones\Models\Requisicion;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Domain\Requisiciones\Enums\RequisicionEstado;

class UnicaRequisicionActivaPorProyectoRule implements ValidationRule
{
    public function __construct(private readonly ?int $requisicionPadreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Si es una enmienda, no aplicamos esta regla restrictiva
        if ($this->requisicionPadreId) {
            return;
        }

        // Verificamos si existe alguna Requisicion activa para este proyecto
        $existeActiva = Requisicion::whereHas('solicitud', function ($query) use ($value) {
            $query->where('proyecto_id', $value);
        })
        ->whereNotIn('estado', [
            RequisicionEstado::CUBIERTA,
            RequisicionEstado::CANCELADA,
        ])
        ->exists();

        if ($existeActiva) {
            $fail('El proyecto seleccionado ya cuenta con una requisición activa. Para realizar cambios, debe generar una modificación (enmienda) en lugar de una requisición nueva.');
        }
    }
}