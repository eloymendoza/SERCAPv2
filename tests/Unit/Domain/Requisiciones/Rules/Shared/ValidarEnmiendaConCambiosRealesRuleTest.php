<?php

use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\Models\DetalleRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\DTOs\RequisicionDTO;
use App\Domain\Requisiciones\DTOs\DetalleRequisicionDTO;
use App\Domain\Requisiciones\Rules\Shared\ValidarEnmiendaConCambiosRealesRule;
use App\Exceptions\Domain\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ValidarEnmiendaConCambiosRealesRule', function () {


it('pasa la validacion si no es una enmienda', function () {
    \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys=OFF;');
    $rule = new ValidarEnmiendaConCambiosRealesRule();
    $dto = SolicitudRequisicionDTO::fromArray([
        'requisicion_padre_id' => null,
    ]);

    // Act & Assert
    $rule->validate(null, $dto);
    expect(true)->toBeTrue();
});

it('lanza excepcion si la enmienda no contiene cambios en los detalles', function () {
    \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys=OFF;');
    // Arrange
    $solicitudPadre = SolicitudRequisicion::create([
        'folio' => 'RP-300',
        'elaborador_id' => 1,
        'solicitante_id' => 1,
        'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,
        'estado' => 'terminado'
    ]);
    $requisicionPadre = Requisicion::create([
        'solicitud_id' => $solicitudPadre->id,
        'fecha_autorizacion' => '2026-01-01',
        'estado' => 'abierta'
    ]);
    
    $tabulador = \App\Domain\Catalogos\Models\TabuladorSalario::firstOrCreate(
        ['id' => 1],
        [
            'id_nivel' => 1,
            'nivel_salarial' => 'N1',
            'nivel_categoria' => 'A',
            'sueldo_minimo' => 10000,
            'sueldo_maximo' => 20000,
            'activo' => true
        ]
    );

    \App\Domain\Autenticacion\Models\User::firstOrCreate(
        ['username' => 'Sistema'],
        ['name' => 'Sistema', 'email' => 'sistema@test.com', 'password' => bcrypt('password')]
    );

    \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly(
        ['nombre' => 'Mock Direccion', 'nivel' => 'direccion', 'estado' => 'Activo']
    );

    \App\Domain\Puestos\Models\Puesto::firstOrCreate(
        ['id' => 1],
        ['nombre_puesto' => 'Mock Puesto', 'tipo' => 'interno', 'estado' => 'activo', 'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,]
    );

    \App\Domain\Requisiciones\Models\Disciplina::firstOrCreate(
        ['id' => 1],
        ['nombre' => 'Mock Disciplina', 'activo' => true]
    );

    $detallePadre = DetalleRequisicion::create([
        'requisicion_id' => $requisicionPadre->id,
        'puesto_id' => 1,
        'cantidad_solicitada' => 2,
        'sueldo_asignado' => 15000,
        'disciplina_id' => 1,
        'tipo_contrato' => 'tiempo_indeterminado',
        'tabulador_id' => 1,
        'turno_horas' => '8',
        'fecha_inicio' => '2026-10-01',
        'fecha_limite_requerimiento' => '2026-10-15',
    ]);

    $detalleDto = DetalleRequisicionDTO::fromArray([
        'puesto_id' => 1,
        'cantidad_solicitada' => 2,
        'sueldo_asignado' => 15000,
        // Resto de atributos requeridos por DTO para evitar errores
        'disciplina_id' => 1,
        'tipo_contrato' => 'tiempo_indeterminado',
        'tabulador_id' => 1,
        'turno_horas' => '8',
        'fecha_inicio' => '2026-10-01',
        'fecha_limite_requerimiento' => '2026-10-15',
    ]);

    $requisicionDto = RequisicionDTO::fromArray([
        'detalles' => [$detalleDto->toArray()]
    ]);

    $dto = SolicitudRequisicionDTO::fromArray([
        'requisicion_padre_id' => $requisicionPadre->id,
        'requisicion' => $requisicionDto->toArray()
    ]);

    $rule = new ValidarEnmiendaConCambiosRealesRule();

    // Act & Assert
    expect(fn () => $rule->validate(null, $dto))
        ->toThrow(BusinessRuleException::class, 'La solicitud de enmienda no contiene modificaciones reales en las cantidades o sueldos respecto a la requisición activa original.');
});

it('pasa la validacion si la enmienda cambia el sueldo', function () {
    \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys=OFF;');
    // Arrange
    $solicitudPadre = SolicitudRequisicion::create([
        'folio' => 'RP-301',
        'elaborador_id' => 1,
        'solicitante_id' => 1,
        'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,
        'estado' => 'terminado'
    ]);
    $requisicionPadre = Requisicion::create([
        'solicitud_id' => $solicitudPadre->id,
        'fecha_autorizacion' => '2026-01-01',
        'estado' => 'abierta'
    ]);
    
    $tabulador = \App\Domain\Catalogos\Models\TabuladorSalario::firstOrCreate(
        ['id' => 1],
        [
            'id_nivel' => 1,
            'nivel_salarial' => 'N1',
            'nivel_categoria' => 'A',
            'sueldo_minimo' => 10000,
            'sueldo_maximo' => 20000,
            'activo' => true
        ]
    );

    \App\Domain\Autenticacion\Models\User::firstOrCreate(
        ['username' => 'Sistema'],
        ['name' => 'Sistema', 'email' => 'sistema@test.com', 'password' => bcrypt('password')]
    );

    \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly(
        ['nombre' => 'Mock Direccion', 'nivel' => 'direccion', 'estado' => 'Activo']
    );

    \App\Domain\Puestos\Models\Puesto::firstOrCreate(
        ['id' => 1],
        ['nombre_puesto' => 'Mock Puesto', 'tipo' => 'interno', 'estado' => 'activo', 'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,]
    );

    \App\Domain\Requisiciones\Models\Disciplina::firstOrCreate(
        ['id' => 1],
        ['nombre' => 'Mock Disciplina', 'activo' => true]
    );

    $detallePadre = DetalleRequisicion::create([
        'requisicion_id' => $requisicionPadre->id,
        'puesto_id' => 1,
        'cantidad_solicitada' => 2,
        'sueldo_asignado' => 15000,
        'disciplina_id' => 1,
        'tipo_contrato' => 'tiempo_indeterminado',
        'tabulador_id' => 1,
        'turno_horas' => '8',
        'fecha_inicio' => '2026-10-01',
        'fecha_limite_requerimiento' => '2026-10-15',
    ]);

    $detalleDto = DetalleRequisicionDTO::fromArray([
        'puesto_id' => 1,
        'cantidad_solicitada' => 2,
        'sueldo_asignado' => 18000, // Cambio de sueldo
        'disciplina_id' => 1,
        'tipo_contrato' => 'tiempo_indeterminado',
        'tabulador_id' => 1,
        'turno_horas' => '8',
        'fecha_inicio' => '2026-10-01',
        'fecha_limite_requerimiento' => '2026-10-15',
    ]);

    $dto = SolicitudRequisicionDTO::fromArray([
        'requisicion_padre_id' => $requisicionPadre->id,
        'requisicion' => ['detalles' => [$detalleDto->toArray()]]
    ]);

    $rule = new ValidarEnmiendaConCambiosRealesRule();

    // Act & Assert
    $rule->validate(null, $dto);
    expect(true)->toBeTrue();
});
});