<?php

namespace App\Domain\Autenticacion\Models;

use Illuminate\Notifications\Notifiable;
use App\Domain\Autenticacion\DTOs\UserDTO;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * //TODO: EMC: Crear el modelo Empleado.php cuando se desarrolle el módulo de Contrataciones.
 * Por ahora usamos User.php para los datos del empleado, pero a futuro 
 * Requisiciones y Workflows deberán enlazarse al nuevo modelo Empleado.
 */
class User extends Authenticatable
{
    public ?UserDTO $contexto = null;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_personal',
        'username',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}