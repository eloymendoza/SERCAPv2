<?php

namespace Tests\Feature\Requisiciones;

use Mockery;
use App\Domain\Autenticacion\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Domain\Workflows\DTOs\WorkflowInstanceDTO;
use App\Domain\Workflows\Services\WorkflowService;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstado;
use App\Domain\Requisiciones\Services\FirmantesResolverService;

uses(RefreshDatabase::class);

/** @var \Tests\TestCase $this */

beforeEach(function () {
    // Aislar bases de datos externas
    config(['database.connections.Costosv2' => config('database.connections.sqlite')]);
});

describe('Aprobación de Solicitud de Requisición', function () {

    it('deniega acceso si la solicitud no esta en_proceso', function () {
        /** @var \Tests\TestCase $this */
        $user = User::create([
            'id_personal' => 100,
            'username' => 'test1',
            'name' => 'test 1',
            'email' => 'test1@test.com',
            'password' => bcrypt('password')
        ]);
        
        $solicitud = SolicitudRequisicion::create([
            'folio' => 'RP-TEST-001',
            'elaborador_id' => $user->id_personal,
            'solicitante_id' => $user->id_personal,
            'direccion_id' => 1,
            'estado' => SolicitudRequisicionEstado::BORRADOR,
            'id_instancia_workflow' => null
        ]);

        $response = $this->actingAs($user)->postJson("/api/requisiciones/solicitudes/{$solicitud->id}/aprobar", []);

        $response->assertStatus(403);
    });

    it('deniega acceso si el usuario no es el firmante actual en Django', function () {
        /** @var \Tests\TestCase $this */
        $user = User::create([
            'id_personal' => 200,
            'username' => 'test2',
            'name' => 'test 2',
            'email' => 'test2@test.com',
            'password' => bcrypt('password')
        ]);
        
        $solicitud = SolicitudRequisicion::create([
            'folio' => 'RP-TEST-002',
            'elaborador_id' => 999,
            'solicitante_id' => 999,
            'direccion_id' => 1,
            'estado' => SolicitudRequisicionEstado::EN_PROCESO,
            'id_instancia_workflow' => 888
        ]);

        $mockWorkflow = Mockery::mock(WorkflowService::class);
        $mockWorkflow->shouldReceive('obtenerFirmanteActual')
            ->with(888)
            ->andReturn(['Id_personal' => 300]); // ID diferente al del usuario

        $this->app->instance(WorkflowService::class, $mockWorkflow);

        $response = $this->actingAs($user)->postJson("/api/requisiciones/solicitudes/{$solicitud->id}/aprobar", [
            'observaciones' => 'OK'
        ]);

        $response->assertStatus(403);
    });

    it('procesa la aprobacion y sincroniza el estado si el usuario es el firmante actual', function () {
        /** @var \Tests\TestCase $this */
        $user = User::create([
            'id_personal' => 400,
            'username' => 'test3',
            'name' => 'test 3',
            'email' => 'test3@test.com',
            'password' => bcrypt('password')
        ]);
        
        $solicitud = SolicitudRequisicion::create([
            'folio' => 'RP-TEST-003',
            'elaborador_id' => 999,
            'solicitante_id' => 999,
            'direccion_id' => 1,
            'estado' => SolicitudRequisicionEstado::EN_PROCESO,
            'id_instancia_workflow' => 777
        ]);

        $mockWorkflow = Mockery::mock(WorkflowService::class);
        
        // Mock de la Policy
        $mockWorkflow->shouldReceive('obtenerFirmanteActual')
            ->with(777)
            ->andReturn([
                'id' => 55,
                'Id_personal' => 400 // Coincide con el usuario
            ]);

        // Mock del Servicio
        $mockWorkflow->shouldReceive('aprobarPaso')
            ->once()
            ->with($solicitud->id, Mockery::on(function ($payload) {
                return $payload['id_instancia'] === 777
                    && $payload['id_firmante'] === 55
                    && $payload['Id_personal'] === 400
                    && $payload['observaciones'] === 'Todo en orden';
            }))
            ->andReturn(new WorkflowInstanceDTO(
                idInstancia: 777,
                workflowId: 1,
                estado: 'Terminado',
                idPersonalEmisor: 999
            ));

        $this->app->instance(WorkflowService::class, $mockWorkflow);

        $response = $this->actingAs($user)->postJson("/api/requisiciones/solicitudes/{$solicitud->id}/aprobar", [
            'observaciones' => 'Todo en orden'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.estado.value', 'terminado');

        $this->assertDatabaseHas('solicitud_requisiciones', [
            'id' => $solicitud->id,
            'estado' => 'terminado'
        ]);
    });

    describe('Rechazo de Solicitud de Requisición', function () {

        it('deniega rechazo si la solicitud no esta en_proceso', function () {
            /** @var \Tests\TestCase $this */
            $user = User::create([
                'id_personal' => 100,
                'username' => 'test1',
                'name' => 'test 1',
                'email' => 'test1@test.com',
                'password' => bcrypt('password')
            ]);
            
            $solicitud = SolicitudRequisicion::create([
                'folio' => 'RP-TEST-004',
                'elaborador_id' => $user->id_personal,
                'solicitante_id' => $user->id_personal,
                'direccion_id' => 1,
                'estado' => SolicitudRequisicionEstado::BORRADOR,
                'id_instancia_workflow' => null
            ]);

            $response = $this->actingAs($user)->postJson("/api/requisiciones/solicitudes/{$solicitud->id}/rechazar", [
                'observaciones' => 'Rechazado por prueba'
            ]);

            $response->assertStatus(403);
        });

        it('deniega rechazo si el usuario no es el firmante actual en Django', function () {
            /** @var \Tests\TestCase $this */
            $user = User::create([
                'id_personal' => 200,
                'username' => 'test2',
                'name' => 'test 2',
                'email' => 'test2@test.com',
                'password' => bcrypt('password')
            ]);
            
            $solicitud = SolicitudRequisicion::create([
                'folio' => 'RP-TEST-005',
                'elaborador_id' => 999,
                'solicitante_id' => 999,
                'direccion_id' => 1,
                'estado' => SolicitudRequisicionEstado::EN_PROCESO,
                'id_instancia_workflow' => 888
            ]);

            $mockWorkflow = Mockery::mock(WorkflowService::class);
            $mockWorkflow->shouldReceive('obtenerFirmanteActual')
                ->with(888)
                ->andReturn(['Id_personal' => 300]);

            $this->app->instance(WorkflowService::class, $mockWorkflow);

            $response = $this->actingAs($user)->postJson("/api/requisiciones/solicitudes/{$solicitud->id}/rechazar", [
                'observaciones' => 'Rechazado por prueba'
            ]);

            $response->assertStatus(403);
        });

        it('procesa el rechazo y cambia el estado a rechazado si el usuario es el firmante actual', function () {
            /** @var \Tests\TestCase $this */
            $user = User::create([
                'id_personal' => 400,
                'username' => 'test3',
                'name' => 'test 3',
                'email' => 'test3@test.com',
                'password' => bcrypt('password')
            ]);
            
            $solicitud = SolicitudRequisicion::create([
                'folio' => 'RP-TEST-006',
                'elaborador_id' => 999,
                'solicitante_id' => 999,
                'direccion_id' => 1,
                'estado' => SolicitudRequisicionEstado::EN_PROCESO,
                'id_instancia_workflow' => 777
            ]);

            $mockWorkflow = Mockery::mock(WorkflowService::class);
            
            $mockWorkflow->shouldReceive('obtenerFirmanteActual')
                ->with(777)
                ->andReturn([
                    'id' => 55,
                    'Id_personal' => 400
                ]);

            $mockWorkflow->shouldReceive('rechazarPaso')
                ->once()
                ->with($solicitud->id, Mockery::on(function ($payload) {
                    return $payload['id_instancia'] === 777
                        && $payload['id_firmante'] === 55
                        && $payload['Id_personal'] === 400
                        && $payload['comentario'] === 'No procede';
                }))
                ->andReturn(new WorkflowInstanceDTO(
                    idInstancia: 777,
                    workflowId: 1,
                    estado: 'Rechazado',
                    idPersonalEmisor: 999
                ));

            $this->app->instance(WorkflowService::class, $mockWorkflow);

            $response = $this->actingAs($user)->postJson("/api/requisiciones/solicitudes/{$solicitud->id}/rechazar", [
                'observaciones' => 'No procede'
            ]);

            $response->assertStatus(200);
            $response->assertJsonPath('data.estado.value', 'rechazado');

            $this->assertDatabaseHas('solicitud_requisiciones', [
                'id' => $solicitud->id,
                'estado' => 'rechazado'
            ]);
        });
    });

    describe('Reinicio de Solicitud de Requisición', function () {

        it('deniega reinicio si la solicitud no esta rechazada', function () {
            /** @var \Tests\TestCase $this */
            $user = User::create([
                'id_personal' => 100,
                'username' => 'test1',
                'name' => 'test 1',
                'email' => 'test1@test.com',
                'password' => bcrypt('password')
            ]);
            
            $solicitud = SolicitudRequisicion::create([
                'folio' => 'RP-TEST-007',
                'elaborador_id' => $user->id_personal,
                'solicitante_id' => $user->id_personal,
                'direccion_id' => 1,
                'estado' => SolicitudRequisicionEstado::BORRADOR,
                'id_instancia_workflow' => null
            ]);

            $response = $this->actingAs($user)->postJson("/api/requisiciones/solicitudes/{$solicitud->id}/reiniciar", [
                'observaciones' => 'Reinicio por corrección'
            ]);

            $response->assertStatus(403);
        });

        it('deniega reinicio si el usuario no es el elaborador', function () {
            /** @var \Tests\TestCase $this */
            $user = User::create([
                'id_personal' => 200,
                'username' => 'test2',
                'name' => 'test 2',
                'email' => 'test2@test.com',
                'password' => bcrypt('password')
            ]);
            
            $solicitud = SolicitudRequisicion::create([
                'folio' => 'RP-TEST-008',
                'elaborador_id' => 999,
                'solicitante_id' => 999,
                'direccion_id' => 1,
                'estado' => SolicitudRequisicionEstado::RECHAZADO,
                'id_instancia_workflow' => 888
            ]);

            $response = $this->actingAs($user)->postJson("/api/requisiciones/solicitudes/{$solicitud->id}/reiniciar", [
                'observaciones' => 'Reinicio por corrección'
            ]);

            $response->assertStatus(403);
        });

        it('procesa el reinicio y cambia el estado a en_proceso si el usuario es el elaborador', function () {
            /** @var \Tests\TestCase $this */
            $user = User::create([
                'id_personal' => 400,
                'username' => 'test3',
                'name' => 'test 3',
                'email' => 'test3@test.com',
                'password' => bcrypt('password')
            ]);
            
            $solicitud = SolicitudRequisicion::create([
                'folio' => 'RP-TEST-009',
                'elaborador_id' => 400,
                'solicitante_id' => 400,
                'direccion_id' => 1,
                'estado' => SolicitudRequisicionEstado::RECHAZADO,
                'id_instancia_workflow' => 777
            ]);

            $mockWorkflow = Mockery::mock(WorkflowService::class);
            
            $mockFirmantesResolver = Mockery::mock(FirmantesResolverService::class);
            $mockFirmantesResolver->shouldReceive('resolverParaRequisicion')
                ->once()
                ->andReturn([
                    'requiere_workflow' => true,
                    'workflow_id' => 1,
                    'firmantes' => [
                        [
                            'Id_personal' => 500,
                            'orden' => 1
                        ]
                    ]
                ]);
            $this->app->instance(FirmantesResolverService::class, $mockFirmantesResolver);

            $mockWorkflow->shouldReceive('reiniciarInstancia')
                ->once()
                ->with($solicitud->id, Mockery::on(function ($payload) {
                    return $payload['id_instancia'] === 777
                        && $payload['Id_personal'] === 400
                        && $payload['observaciones'] === 'Corregido'
                        && !empty($payload['firmantes'])
                        && $payload['firmantes'][0]['Id_personal'] === 500;
                }))
                ->andReturn(new WorkflowInstanceDTO(
                    idInstancia: 777,
                    workflowId: 1,
                    estado: 'En Proceso',
                    idPersonalEmisor: 400
                ));

            $this->app->instance(WorkflowService::class, $mockWorkflow);

            $response = $this->actingAs($user)->postJson("/api/requisiciones/solicitudes/{$solicitud->id}/reiniciar", [
                'observaciones' => 'Corregido'
            ]);

            $response->assertStatus(200);
            $response->assertJsonPath('data.estado.value', 'en_proceso');

            $this->assertDatabaseHas('solicitud_requisiciones', [
                'id' => $solicitud->id,
                'estado' => 'en_proceso'
            ]);
        });
    });

});