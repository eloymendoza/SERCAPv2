<?php

namespace App\Domain\Autenticacion\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Domain\Requisiciones\DTOs\ContextoAutorizacionDTO;
use App\Domain\Requisiciones\Services\VinculoContextualService;

class User extends Authenticatable
{
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

    // --- Vinculación contextual ---

    /**
     * Determina si el usuario es el encargado de una dirección organizativa específica.
     */
    public function esDireccionEncargada(int $direccionId): bool
    {
        return app(VinculoContextualService::class)->esDireccionEncargada($this, $direccionId);
    }

    /**
     * Determina si el usuario funge como gerente o jefe de un proyecto específico activo.
     */
    public function esVinculadoProyecto(int $proyectoId): bool
    {
        return app(VinculoContextualService::class)->esVinculadoProyecto($this, $proyectoId);
    }

    /**
     * Determina si el usuario tiene vínculo operativo con la dirección o proyecto dados.
     */
    public function tieneVinculoContextual(?int $direccionId, ?int $proyectoId): bool
    {
        $contexto = new ContextoAutorizacionDTO($this, $direccionId, $proyectoId);
        return app(VinculoContextualService::class)->tieneVinculoContextual($contexto);
    }
}