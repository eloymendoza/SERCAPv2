<?php

use App\Domain\Requisiciones\Enums\RequisicionEstadoEnum;
use App\Domain\Catalogos\Models\Proyecto;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\Rules\Create\ValidarUnicaRequisicionActivaPorProyectoRule;
use App\Exceptions\Domain\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ValidarUnicaRequisicionActivaPorProyectoRule', function () {

beforeEach(function () {
    // Redirigir conexión Costosv2 a sqlite en memoria
    config(['database.connections.Costosv2' => config('database.connections.sqlite')]);

    // Crear la tabla externa de proyectos en la base de datos de pruebas
    \Illuminate\Support\Facades\Schema::connection('Costosv2')->dropIfExists('proyecto');
    \Illuminate\Support\Facades\Schema::connection('Costosv2')->create('proyecto', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->id('idProyecto');
        $table->string('proyecto');
        $table->string('descripcion')->nullable();
        $table->string('lugar')->nullable();
        $table->string('cliente')->nullable();
        $table->string('jefeProyecto')->nullable();
        $table->date('fechaInicio')->nullable();
        $table->date('fechaTermino')->nullable();
        $table->string('estado')->nullable();
        $table->boolean('activoProyecto')->default(true);
        $table->string('numeroProyectoSap')->nullable();
        $table->string('sociedad')->nullable();
        $table->string('gerenteProyecto')->nullable();
    });
});
it('pasa la validacion si se trata de una enmienda', function () {
    $rule = new ValidarUnicaRequisicionActivaPorProyectoRule();
    $dto = SolicitudRequisicionDTO::fromArray([
        'requisicion_padre_id' => 1,
        'proyecto_id' => 10,
    ]);

    // Act & Assert
    $rule->validate(null, $dto);
    expect(true)->toBeTrue();
});

it('lanza excepcion si ya existe una requisicion activa para el proyecto', function () {
    // Arrange
    $proyecto = Proyecto::create([
        'idProyecto' => 10,
        'proyecto' => 'Proyecto Test',
        'activoProyecto' => true
    ]);
    $solicitudExistente = SolicitudRequisicion::create([
        'folio' => 'RP-200',
        'elaborador_id' => 1,
        'solicitante_id' => 1,
        'direccion_id' => 1,
        'proyecto_id' => $proyecto->idProyecto,
        'estado' => 'terminado'
    ]);
    
    Requisicion::create([
        'solicitud_id' => $solicitudExistente->id,
        'estado' => RequisicionEstadoEnum::ABIERTA,
    ]);

    $dto = SolicitudRequisicionDTO::fromArray([
        'requisicion_padre_id' => null, // No es enmienda
        'proyecto_id' => $proyecto->idProyecto,
    ]);

    $rule = new ValidarUnicaRequisicionActivaPorProyectoRule();

    // Act & Assert
    expect(fn () => $rule->validate(null, $dto))
        ->toThrow(BusinessRuleException::class, 'El proyecto seleccionado ya cuenta con una requisición activa. Para realizar cambios, debe generar una modificación (enmienda) en lugar de una requisición nueva.');
});

it('pasa la validacion si las requisiciones existentes del proyecto estan cubiertas o canceladas', function () {
    // Arrange
    $proyecto = Proyecto::create([
        'idProyecto' => 11,
        'proyecto' => 'Proyecto Test 2',
        'activoProyecto' => true
    ]);
    $solicitudExistente = SolicitudRequisicion::create([
        'folio' => 'RP-201',
        'elaborador_id' => 1,
        'solicitante_id' => 1,
        'direccion_id' => 1,
        'proyecto_id' => $proyecto->idProyecto,
        'estado' => 'terminado'
    ]);
    
    Requisicion::create([
        'solicitud_id' => $solicitudExistente->id,
        'estado' => RequisicionEstadoEnum::CUBIERTA,
    ]);

    $dto = SolicitudRequisicionDTO::fromArray([
        'requisicion_padre_id' => null,
        'proyecto_id' => $proyecto->idProyecto,
    ]);

    $rule = new ValidarUnicaRequisicionActivaPorProyectoRule();

    // Act & Assert
    $rule->validate(null, $dto);
    expect(true)->toBeTrue();
});
});