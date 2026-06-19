<?php

namespace App\App\Api\Requisiciones\Rules;

use Closure;
use App\Domain\Catalogos\Models\TabuladorSalario;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidarRangoSueldoTabuladorRule implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Valida que el sueldo asignado se encuentre dentro de los límites del tabulador seleccionado.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parts = explode('.', $attribute);
        if (count($parts) < 3) {
            return;
        }

        $index = $parts[2];
        $tabuladorId = data_get($this->data, "requisicion.detalle.{$index}.tabulador_id");

        if (! $tabuladorId) {
            return;
        }

        $tabulador = TabuladorSalario::find($tabuladorId);

        if ($tabulador && ($value < $tabulador->sueldo_minimo || $value > $tabulador->sueldo_maximo)) {
            $posicion = (int)$index + 1;
            $fail("El sueldo asignado en la vacante #{$posicion} debe estar estrictamente entre \$ {$tabulador->sueldo_minimo} y \$ {$tabulador->sueldo_maximo}.");
        }
    }
}