<?php

use App\Domain\Catalogos\Models\TabuladorSalario;
use App\Domain\Requisiciones\DTOs\DetalleRequisicionDTO;
use App\Domain\Requisiciones\DTOs\RequisicionDTO;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\Rules\Shared\ValidarRangoSueldoTabuladorRule;
use App\Exceptions\Domain\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ValidarRangoSueldoTabuladorRule', function () {

    it('valida el sueldo asignado contra los limites del tabulador', function (int $sueldoAsignado, bool $debeLanzarExcepcion) {
        // Arrange
        $tabulador = TabuladorSalario::firstOrCreate(
            ['id_nivel' => 1],
            [
                'nivel_salarial' => 'N1',
                'nivel_categoria' => 'A',
                'sueldo_minimo' => 10000,
                'sueldo_maximo' => 15000,
                'activo' => true
            ]
        );

        $detalleDto = DetalleRequisicionDTO::fromArray([
            'cantidad_solicitada' => 1,
            'disciplina_id' => 1,
            'tipo_contrato' => 'tiempo_indeterminado',
            'tabulador_id' => $tabulador->id,
            'sueldo_asignado' => $sueldoAsignado,
            'turno_horas' => '8',
            'fecha_inicio' => '2026-10-01',
            'fecha_limite_requerimiento' => '2026-10-15',
        ]);

        $requisicionDto = RequisicionDTO::fromArray([
            'detalles' => [$detalleDto->toArray()]
        ]);

        $solicitudDto = SolicitudRequisicionDTO::fromArray([
            'requisicion' => $requisicionDto->toArray()
        ]);

        $rule = new ValidarRangoSueldoTabuladorRule();

        // Act & Assert
        if ($debeLanzarExcepcion) {
            expect(fn () => $rule->validate(null, $solicitudDto))
                ->toThrow(BusinessRuleException::class, "El sueldo asignado en la vacante #1 debe estar estrictamente entre \$ {$tabulador->sueldo_minimo} y \$ {$tabulador->sueldo_maximo}.");
        } else {
            $rule->validate(null, $solicitudDto);
            expect(true)->toBeTrue();
        }
    })->with([
        'sueldo menor al minimo' => [9000, true],
        'sueldo mayor al maximo' => [16000, true],
        'sueldo limite inferior (valido)' => [10000, false],
        'sueldo intermedio (valido)' => [12000, false],
        'sueldo limite superior (valido)' => [15000, false],
    ]);

});