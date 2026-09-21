<?php

use App\Domain\Autenticacion\Models\User;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\Rules\Shared\ValidarVinculoProyectoRule;
use App\Domain\Requisiciones\Services\VinculoContextualService;
use App\Exceptions\Domain\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

describe('ValidarVinculoProyectoRule', function () {

it('pasa la validacion si el usuario no esta seteado', function () {
    $rule = new ValidarVinculoProyectoRule();
    $dto = SolicitudRequisicionDTO::fromArray([
        'solicitante_id' => null,
    ]);

    // Act & Assert
    $rule->validate(null, $dto);
    expect(true)->toBeTrue();
});

it('lanza excepcion si el usuario no existe', function () {
    $dto = SolicitudRequisicionDTO::fromArray([
        'solicitante_id' => 999,
        'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,
        'proyecto_id' => 10,
    ]);

    $rule = new ValidarVinculoProyectoRule();

    // Act & Assert
    expect(fn () => $rule->validate(null, $dto))
        ->toThrow(BusinessRuleException::class, 'El solicitante especificado no es un usuario válido del sistema.');
});

it('lanza excepcion si el usuario no tiene vinculo contextual', function () {
    // Arrange
    $user = User::create([
        'id_personal' => 100,
        'username' => 'test',
        'name' => 'test',
        'email' => 'test@test.com',
        'password' => bcrypt('password')
    ]);

    $dto = SolicitudRequisicionDTO::fromArray([
        'solicitante_id' => 100,
        'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,
        'proyecto_id' => 10,
    ]);

    $mockVinculoService = Mockery::mock(VinculoContextualService::class);
    $mockVinculoService->shouldReceive('tieneVinculoContextual')
        ->once()
        ->andReturn(false);

    $rule = new ValidarVinculoProyectoRule($mockVinculoService);

    // Act & Assert
    expect(fn () => $rule->validate(null, $dto))
        ->toThrow(BusinessRuleException::class, 'El solicitante debe estar directamente ligado a la dirección (como Director) o al proyecto (como Gerente/Jefe de Proyecto). No se admiten solicitantes ajenos.');
});

it('pasa la validacion si el usuario tiene vinculo contextual', function () {
    // Arrange
    $user = User::create([
        'id_personal' => 100,
        'username' => 'test2',
        'name' => 'test',
        'email' => 'test2@test.com',
        'password' => bcrypt('password')
    ]);

    $dto = SolicitudRequisicionDTO::fromArray([
        'solicitante_id' => 100,
        'unidad_organizativa_id' => \App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa::factory()->createQuietly()->id,
        'proyecto_id' => 10,
    ]);

    $mockVinculoService = Mockery::mock(VinculoContextualService::class);
    $mockVinculoService->shouldReceive('tieneVinculoContextual')
        ->once()
        ->andReturn(true);

    $rule = new ValidarVinculoProyectoRule($mockVinculoService);

    // Act & Assert
    $rule->validate(null, $dto);
    expect(true)->toBeTrue();
});
});
