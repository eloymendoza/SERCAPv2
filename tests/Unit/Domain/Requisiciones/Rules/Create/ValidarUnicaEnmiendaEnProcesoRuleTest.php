<?php

use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstadoEnum;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\Rules\Create\ValidarUnicaEnmiendaEnProcesoRule;
use App\Exceptions\Domain\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ValidarUnicaEnmiendaEnProcesoRule', function () {

it('pasa la validacion si no es una enmienda', function () {
    $rule = new ValidarUnicaEnmiendaEnProcesoRule();
    $dto = SolicitudRequisicionDTO::fromArray([
        'requisicion_padre_id' => null,
    ]);

    // Act & Assert
    $rule->validate(null, $dto);
    expect(true)->toBeTrue();
});

it('lanza excepcion si ya existe una enmienda en borrador para la misma requisicion padre', function () {
    // Arrange
    $solicitudPadre = SolicitudRequisicion::create([
        'folio' => 'RP-100',
        'elaborador_id' => 1,
        'solicitante_id' => 1,
        'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,
        'estado' => SolicitudRequisicionEstadoEnum::TERMINADO,
    ]);
    $requisicionPadre = Requisicion::create([
        'solicitud_id' => $solicitudPadre->id,
        'fecha_autorizacion' => '2026-01-01',
        'estado' => 'abierta'
    ]);

    // Enmienda existente en borrador
    SolicitudRequisicion::create([
        'folio' => 'RP-100-ENM1',
        'elaborador_id' => 1,
        'solicitante_id' => 1,
        'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,
        'requisicion_padre_id' => $requisicionPadre->id,
        'estado' => SolicitudRequisicionEstadoEnum::BORRADOR,
    ]);

    $dto = SolicitudRequisicionDTO::fromArray([
        'requisicion_padre_id' => $requisicionPadre->id,
    ]);

    $rule = new ValidarUnicaEnmiendaEnProcesoRule();

    // Act & Assert
    expect(fn () => $rule->validate(null, $dto))
        ->toThrow(BusinessRuleException::class, 'La requisición seleccionada ya tiene una solicitud o modificación en curso (o rechazada pendiente de corrección). No puede crear otra hasta que la actual sea terminada o cancelada.');
});

it('pasa la validacion si la enmienda anterior ya esta terminada', function () {
    // Arrange
    $solicitudPadre = SolicitudRequisicion::create([
        'folio' => 'RP-101',
        'elaborador_id' => 1,
        'solicitante_id' => 1,
        'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,
        'estado' => SolicitudRequisicionEstadoEnum::TERMINADO,
    ]);
    $requisicionPadre = Requisicion::create([
        'solicitud_id' => $solicitudPadre->id,
        'fecha_autorizacion' => '2026-01-01',
        'estado' => 'abierta'
    ]);

    SolicitudRequisicion::create([
        'folio' => 'RP-101-ENM1',
        'elaborador_id' => 1,
        'solicitante_id' => 1,
        'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,
        'requisicion_padre_id' => $requisicionPadre->id,
        'estado' => SolicitudRequisicionEstadoEnum::TERMINADO,
    ]);

    $dto = SolicitudRequisicionDTO::fromArray([
        'requisicion_padre_id' => $requisicionPadre->id,
    ]);

    $rule = new ValidarUnicaEnmiendaEnProcesoRule();

    // Act & Assert
    $rule->validate(null, $dto);
    expect(true)->toBeTrue();
});
});
