<?php

namespace Tests\Feature\Requisiciones;

use Illuminate\Support\Facades\Schema;
use App\Domain\Autenticacion\Models\User;
use App\Domain\Catalogos\Models\Proyecto;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Domain\Requisiciones\DTOs\ContextoAutorizacionDTO;
use App\Domain\Requisiciones\Services\VinculoContextualService;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

uses(RefreshDatabase::class);

/** @var \Tests\TestCase $this */

beforeEach(function () {
    // Aislar bases de datos externas
    config(['database.connections.Costosv2' => config('database.connections.sqlite')]);

    // Crear el usuario Sistema para evitar errores de Foreign Key en el historial
    User::firstOrCreate(
        ['username' => 'Sistema'],
        [
            'id_personal' => 0,
            'name' => 'Usuario Sistema',
            'email' => 'sistema@test.com',
            'password' => bcrypt('password')
        ]
    );

    // Crear la tabla de proyectos para poder realizar pruebas de Eloquent en aislamiento
    Schema::connection('Costosv2')->dropIfExists('proyecto');
    Schema::connection('Costosv2')->create('proyecto', function (Blueprint $table) {
        $table->id('idProyecto');
        $table->string('proyecto');
        $table->string('gerenteProyecto')->nullable();
        $table->string('jefeProyecto')->nullable();
        $table->boolean('activoProyecto')->default(true);
    });
});

describe('VinculoContextualService', function () {

    it('determina correctamente si un usuario es encargado de una direccion', function () {
        $user = User::create([
            'id_personal' => 123,
            'username' => 'eloy.mendoza',
            'name' => 'Eloy Mendoza Cortez',
            'email' => 'eloy@test.com'
        ]);

        $direccion = UnidadOrganizativa::create([
            'nivel' => 'direccion',
            'nombre' => 'Dirección de Desarrollo',
            'encargado_usuario' => 'eloy.mendoza',
            'estado' => 'Activo'
        ]);

        $service = new VinculoContextualService();

        expect($service->esDireccionEncargada($user, $direccion->id))->toBeTrue()
            ->and($service->esDireccionEncargada($user, 999))->toBeFalse();
    });

    it('determina correctamente si un usuario es gerente o jefe de un proyecto', function () {
        $user = User::create([
            'id_personal' => 123,
            'username' => 'eloy.mendoza',
            'name' => 'Eloy Mendoza Cortez',
            'email' => 'eloy@test.com'
        ]);

        $proyecto = Proyecto::create([
            'idProyecto' => 456,
            'proyecto' => 'Sistema SERCAP',
            'gerenteProyecto' => 'Eloy Mendoza Cortez',
            'jefeProyecto' => 'OTRO',
            'activoProyecto' => true
        ]);

        $service = new VinculoContextualService();

        expect($service->esVinculadoProyecto($user, $proyecto->idProyecto))->toBeTrue()
            ->and($service->esVinculadoProyecto($user, 999))->toBeFalse();
    });

    it('evalua vinculo contextual completo', function () {
        $user = User::create([
            'id_personal' => 123,
            'username' => 'eloy.mendoza',
            'name' => 'Eloy Mendoza Cortez',
            'email' => 'eloy@test.com'
        ]);

        $service = new VinculoContextualService();

        // Sin parámetros
        $contextoVacio = new ContextoAutorizacionDTO($user, null, null);
        expect($service->tieneVinculoContextual($contextoVacio))->toBeFalse();

        // Con parámetros inválidos
        $contextoInvalido = new ContextoAutorizacionDTO($user, 999, 999);
        expect($service->tieneVinculoContextual($contextoInvalido))->toBeFalse();
    });

});