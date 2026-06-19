<?php

namespace Tests\Feature\Requisiciones;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Domain\Autenticacion\Models\User;
use Illuminate\Database\Schema\Blueprint;
use App\Domain\Catalogos\Models\Proyecto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\App\Api\Requisiciones\Rules\ValidarVinculoProyectoRule;
use App\Domain\Requisiciones\Policies\SolicitudRequisicionPolicy;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

/**
 * Pruebas de integración para la autorización contextual.
 * Valida reglas de acceso por Dirección y Proyecto para elaboradores y solicitantes.
 */
uses(RefreshDatabase::class);

/** @var \Tests\TestCase $this */

beforeEach(function () {
    // Redirigir conexión Costosv2 a sqlite en memoria
    config(['database.connections.Costosv2' => config('database.connections.sqlite')]);

    // Crear la tabla externa de proyectos en la base de datos de pruebas
    Schema::connection('Costosv2')->dropIfExists('proyecto');
    Schema::connection('Costosv2')->create('proyecto', function (Blueprint $table) {
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

describe('Métodos Contextuales de User', function () {

    it('identifica si el usuario es encargado de una dirección específica', function () {
        $user = User::create([
            'id_personal' => 100,
            'username' => 'juan.perez',
            'name' => 'JUAN PEREZ',
            'email' => 'juan.perez@test.com',
        ]);

        $otraDireccion = UnidadOrganizativa::create([
            'nivel' => 'direccion',
            'nombre' => 'Dirección B',
            'encargado_id' => 200,
            'estado' => 'Activo'
        ]);

        $suDireccion = UnidadOrganizativa::create([
            'parent_id' => null,
            'nivel' => 'direccion',
            'nombre' => 'Dirección A',
            'encargado_id' => 100,
            'estado' => 'Activo'
        ]);

        expect($user->esDireccionEncargada($suDireccion->id))->toBeTrue()
            ->and($user->esDireccionEncargada($otraDireccion->id))->toBeFalse();
    });

    it('identifica si el usuario está vinculado a un proyecto como gerente o jefe', function () {
        $user = User::create([
            'id_personal' => 100,
            'username' => 'juan.perez',
            'name' => 'JUAN PEREZ',
            'email' => 'juan.perez@test.com',
        ]);

        $proyectoGerente = Proyecto::create([
            'idProyecto' => 1,
            'proyecto' => 'Proyecto A',
            'gerenteProyecto' => 'JUAN PEREZ',
            'jefeProyecto' => 'OTRO',
            'activoProyecto' => true
        ]);

        $proyectoJefe = Proyecto::create([
            'idProyecto' => 2,
            'proyecto' => 'Proyecto B',
            'gerenteProyecto' => 'OTRO',
            'jefeProyecto' => 'JUAN PEREZ',
            'activoProyecto' => true
        ]);

        $proyectoNoVinculado = Proyecto::create([
            'idProyecto' => 3,
            'proyecto' => 'Proyecto C',
            'gerenteProyecto' => 'OTRO',
            'jefeProyecto' => 'OTRO',
            'activoProyecto' => true
        ]);

        $proyectoInactivo = Proyecto::create([
            'idProyecto' => 4,
            'proyecto' => 'Proyecto D',
            'gerenteProyecto' => 'JUAN PEREZ',
            'activoProyecto' => false
        ]);

        expect($user->esVinculadoProyecto($proyectoGerente->idProyecto))->toBeTrue()
            ->and($user->esVinculadoProyecto($proyectoJefe->idProyecto))->toBeTrue()
            ->and($user->esVinculadoProyecto($proyectoNoVinculado->idProyecto))->toBeFalse()
            ->and($user->esVinculadoProyecto($proyectoInactivo->idProyecto))->toBeFalse();
    });

});

describe('SolicitudRequisicionPolicy', function () {

    it('permite crear a usuarios con rol EAP sin restricciones', function () {
        $user = User::create([
            'id_personal' => 100,
            'username' => 'juan.perez',
            'name' => 'JUAN PEREZ',
            'email' => 'juan.perez@test.com',
        ]);
        Gate::define('EAP', fn () => true);

        $policy = app(SolicitudRequisicionPolicy::class);
        expect($policy->create($user, 999, 999))->toBeTrue();
    });

    it('evalúa correctamente el vínculo de dirección/proyecto para directores y gerentes en create', function () {
        $user = User::create([
            'id_personal' => 100,
            'username' => 'juan.perez',
            'name' => 'JUAN PEREZ',
            'email' => 'juan.perez@test.com',
        ]);
        Gate::define('EAP', fn () => false);

        $direccion = UnidadOrganizativa::create([
            'nivel' => 'direccion',
            'nombre' => 'Dirección A',
            'encargado_id' => 100,
            'estado' => 'Activo'
        ]);

        $proyecto = Proyecto::create([
            'idProyecto' => 1,
            'proyecto' => 'Proyecto A',
            'gerenteProyecto' => 'JUAN PEREZ',
            'activoProyecto' => true
        ]);

        $policy = app(SolicitudRequisicionPolicy::class);

        expect($policy->create($user, $direccion->id, null))->toBeTrue()
            ->and($policy->create($user, null, $proyecto->idProyecto))->toBeTrue()
            ->and($policy->create($user, 999, 999))->toBeFalse();
    });

    it('permite actualizar sólo al elaborador original', function () {
        $userElaborador = User::create([
            'id_personal' => 100,
            'username' => 'juan.perez',
            'name' => 'JUAN PEREZ',
            'email' => 'juan.perez@test.com',
        ]);
        $userOtro = User::create([
            'id_personal' => 200,
            'username' => 'pedro.gomez',
            'name' => 'PEDRO GOMEZ',
            'email' => 'pedro@test.com',
        ]);

        $solicitud = new SolicitudRequisicion();
        $solicitud->elaborador_id = 100;
        $solicitud->solicitante_id = 200;

        $policy = app(SolicitudRequisicionPolicy::class);

        expect($policy->update($userElaborador, $solicitud))->toBeTrue()
            ->and($policy->update($userOtro, $solicitud))->toBeFalse();
    });

});

describe('ValidarVinculoProyectoRule', function () {

    it('pasa la validación si el solicitante tiene un vínculo contextual', function () {
        $usuario = User::create([
            'id_personal' => 300,
            'username' => 'pedro.gomez',
            'name' => 'PEDRO GOMEZ',
            'email' => 'pedro@test.com',
        ]);

        $direccion = UnidadOrganizativa::create([
            'nivel' => 'direccion',
            'nombre' => 'Dirección A',
            'encargado_id' => 300,
            'estado' => 'Activo'
        ]);

        $rule = new ValidarVinculoProyectoRule($direccion->id, null);
        
        $rule->validate('solicitante_id', 300, function ($message) {
            $this->fail("La validación debió pasar, pero falló con: $message");
        });

        expect(true)->toBeTrue();
    });

    it('falla la validación si el solicitante no tiene un vínculo contextual', function () {
        $usuario = User::create([
            'id_personal' => 300,
            'username' => 'pedro.gomez',
            'name' => 'PEDRO GOMEZ',
            'email' => 'pedro@test.com',
        ]);

        $rule = new ValidarVinculoProyectoRule(999, 999);

        $failed = false;
        $rule->validate('solicitante_id', 300, function ($message) use (&$failed) {
            $failed = true;
            expect($message)->toContain('El solicitante debe estar directamente ligado');
        });

        expect($failed)->toBeTrue();
    });

    it('falla la validación si el solicitante no existe', function () {
        $rule = new ValidarVinculoProyectoRule(null, null);

        $failed = false;
        $rule->validate('solicitante_id', 999, function ($message) use (&$failed) {
            $failed = true;
            expect($message)->toContain('El solicitante especificado no es un usuario válido');
        });

        expect($failed)->toBeTrue();
    });

});

describe('Integración HTTP (Request y Endpoint)', function () {

    it('permite que un usuario EAP acceda al validador y reciba 422 si proyecto_id no existe', function () {
        /** @var \Tests\TestCase $this */
        $user = User::create([
            'id_personal' => 100,
            'username' => 'juan.perez',
            'name' => 'JUAN PEREZ',
            'email' => 'juan.perez@test.com',
        ]);
        Gate::define('EAP', fn () => true);

        // Crear dirección activa necesaria para la validación
        $direccion = UnidadOrganizativa::create([
            'nivel' => 'direccion',
            'nombre' => 'Dirección A',
            'encargado_id' => 100,
            'estado' => 'Activo'
        ]);

        $this->actingAs($user);

        // Enviar proyecto_id inexistente (999)
        $response = $this->postJson('/api/requisiciones/solicitudes', [
            'direccion_id' => $direccion->id,
            'proyecto_id' => 999,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'proyecto_id' => [
                            'El proyecto seleccionado no existe.'
                        ]
                    ]
                ]
            ]);
    });

    it('deniega acceso con 403 a usuarios comunes sin vínculo contextual', function () {
        /** @var \Tests\TestCase $this */
        $user = User::create([
            'id_personal' => 100,
            'username' => 'juan.perez',
            'name' => 'JUAN PEREZ',
            'email' => 'juan.perez@test.com',
        ]);
        Gate::define('EAP', fn () => false);

        $direccion = UnidadOrganizativa::create([
            'nivel' => 'direccion',
            'nombre' => 'Dirección A',
            'encargado_id' => 200, // Pertenece a otro encargado
            'estado' => 'Activo'
        ]);

        $this->actingAs($user);

        // Intentar crear sin tener vínculo
        $response = $this->postJson('/api/requisiciones/solicitudes', [
            'direccion_id' => $direccion->id,
            'proyecto_id' => null,
        ]);

        $response->assertStatus(403);
    });

});

