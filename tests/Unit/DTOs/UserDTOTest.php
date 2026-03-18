<?php

namespace Tests\Unit\DTOs;

use App\DTOs\UserDTO;

/**
 * Pruebas de unidad para UserDTO.
 * Valida integridad de datos, manejo de nulos e inmutabilidad.
 */
describe('UserDTO', function () {

    it('crea correctamente un DTO a partir de una matriz', function () {
        $data = [
            'id' => 10,
            'idPersonal' => 11018,
            'username' => 'eloy.mendoza',
            'name' => 'ELOY MENDOZA CORTEZ',
            'email' => 'eloy.mendoza@email.com',
            'puestoActual' => 'AUXILIAR DE DESARROLLO',
            'rutaFoto' => '11018.jpg',
            'permisos' => ['loginSERCAPV2'],
            'token' => 'super_secret_token'
        ];

        $dto = UserDTO::fromArray($data);

        expect($dto->id)->toBe(10)
            ->and($dto->idPersonal)->toBe(11018)
            ->and($dto->username)->toBe('eloy.mendoza')
            ->and($dto->name)->toBe('ELOY MENDOZA CORTEZ')
            ->and($dto->puestoActual)->toBe('AUXILIAR DE DESARROLLO')
            ->and($dto->permisos)->toContain('loginSERCAPV2')
            ->and($dto->token)->toBe('super_secret_token');
    });

    it('maneja correctamente claves de matriz faltantes', function () {
        $dto = UserDTO::fromArray(['username' => 'test.user']);

        expect($dto->username)->toBe('test.user')
            ->and($dto->id)->toBeNull()
            ->and($dto->permisos)->toBeEmpty()
            ->and($dto->email)->toBe('');
    });

    it('crea una nueva instancia cuando usa withId (inmutabilidad)', function () {
        $originalDto = new UserDTO(
            id: null,
            idPersonal: 11018,
            username: 'eloy.mendoza',
            name: 'Eloy',
            email: 'eloy@test.com'
        );

        $newDto = $originalDto->withId(10);

        expect($newDto->id)->toBe(10)
            ->and($newDto->username)->toBe('eloy.mendoza')
            ->and($originalDto->id)->toBeNull()
            ->and($newDto)->not->toBe($originalDto);
    });

});