<?php

namespace App\Domain\Requisiciones\Rules\Shared;

use App\Exceptions\Domain\BusinessRuleException;
use App\Domain\Catalogos\Models\TabuladorSalario;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\Rules\SolicitudRequisicionRuleInterface;

class ValidarRangoSueldoTabuladorRule implements SolicitudRequisicionRuleInterface
{
    /**
     * Valida que el sueldo asignado a cada vacante se encuentre dentro 
     * de los límites del tabulador seleccionado.
     */
    public function validate(?SolicitudRequisicion $model, ?SolicitudRequisicionDTO $dto = null): void
    {
        if (!$dto || !$dto->requisicion || empty($dto->requisicion->detalles)) {
            return;
        }

        foreach ($dto->requisicion->detalles as $index => $detalleDto) {
            $tabuladorId = $detalleDto->tabuladorId;

            if (!$tabuladorId) {
                continue;
            }

            $tabulador = TabuladorSalario::find($tabuladorId);
            $sueldoAsignado = $detalleDto->sueldoAsignado;

            if ($tabulador && ($sueldoAsignado < $tabulador->sueldo_minimo || $sueldoAsignado > $tabulador->sueldo_maximo)) {
                $posicion = $index + 1;
                throw BusinessRuleException::withMessage(
                    "El sueldo asignado en la vacante #{$posicion} debe estar estrictamente entre \$ {$tabulador->sueldo_minimo} y \$ {$tabulador->sueldo_maximo}.",
                    'SUELDO_FUERA_DE_RANGO'
                );
            }
        }
    }
}