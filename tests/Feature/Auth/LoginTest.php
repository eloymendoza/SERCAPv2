<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** @var \Tests\TestCase $this */

/**
 * Pruebas de integración para autenticación con API Auth.
 * Valida sincronización de datos, persistencia en BD y gestión de sesiones.
 */
uses(RefreshDatabase::class);

describe('Flujo de Autenticación (Login)', function () {

    it('permite iniciar sesión si las credenciales de Django son válidas', function () {
        /** @var \Tests\TestCase $this */
        $username = 'eloy.mendoza';
        $password = 'password123';
        
        $mockResponse = [
            'message' => 'Success',
            'idPersonal' => '11018',
            'nNoEmpleado' => '110183',
            'usuario' => $username,
            'nombreCompleto' => 'ELOY MENDOZA CORTEZ',
            'token' => 'fake-django-token',
            'permisos' => ['loginSERCAPV2'],
            'rutaFoto' => '11018.jpg',
            'puestoActual' => 'AUXILIAR DE DESARROLLO'
        ];

        Http::fake(['*' => Http::response($mockResponse, 200)]);

        // Se requiere 'Referer' y una sesión iniciada para que Sanctum considere la petición como 'stateful'
        $response = $this->withSession([])
            ->withHeader('Referer', 'http://localhost')
            ->postJson('/api/login', [
                'username' => $username,
                'password' => $password,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'idPersonal',
                    'usuario',
                    'nombreCompleto',
                    'email',
                    'puestoActual',
                    'rutaFoto',
                    'permisos'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'username' => $username,
            'id_personal' => 11018
        ]);

        $this->assertAuthenticated();
    });

    it('rechaza el inicio de sesión si Django devuelve un error de credenciales', function () {
        /** @var \Tests\TestCase $this */
        Http::fake([
            config('services.django_auth.base_url') => Http::response(['message' => 'Invalid credentials'], 401)
        ]);

        $response = $this->withSession([])
            ->withHeader('Referer', 'http://localhost')
            ->postJson('/api/login', [
                'username' => 'usuario.malo',
                'password' => 'incorrecta',
            ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error' => ['code' => 'AUTH_INVALID_CREDENTIALS']
            ]);

        $this->assertGuest();
    });

});