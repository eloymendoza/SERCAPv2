<?php

namespace App\Domain\Autenticacion\Models;

use App\Domain\Catalogos\Models\Proyecto;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

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

    /**
     * Determina si el usuario es director en la estructura organizativa.
     */
    public function isDirector(): bool
    {
        return UnidadOrganizativa::where('encargado_id', $this->id_personal)
            ->where('nivel', 'direccion')
            ->exists();
    }

    /**
     * Determina si el usuario es gerente en la estructura organizativa.
     */
    public function isGerente(): bool
    {
        return UnidadOrganizativa::where('encargado_id', $this->id_personal)
            ->where('nivel', 'gerencia')
            ->exists();
    }

    /**
     * Determina si el usuario funge como gerente de algún proyecto activo.
     */
    public function isGerenteProyecto(): bool
    {
        return Proyecto::where('activoProyecto', true)
            ->where('gerenteProyecto', $this->name)
            ->exists();
    }

    /**
     * Determina si el usuario funge como jefe de algún proyecto activo.
     */
    public function isJefeProyecto(): bool
    {
        return Proyecto::where('activoProyecto', true)
            ->where('jefeProyecto', $this->name)
            ->exists();
    }
}