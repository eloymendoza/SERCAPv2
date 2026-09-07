<?php

namespace App\Domain\EstructuraOrganizacional\Models;

use App\Domain\Autenticacion\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnidadOrganizativaHistorialCambio extends Model
{
    protected $table = 'unidad_organizativa_historial_cambios';
    public $timestamps = false; // Solo usamos created_at gestionado manualmente o por DB

    protected $fillable = [
        'unidad_organizativa_id',
        'event',
        'old_values',
        'new_values',
        'username',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Obtiene la unidad organizativa a la que pertenece este cambio.
     */
    public function unidadOrganizativa(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizativa::class, 'unidad_organizativa_id');
    }

    /**
     * Resuelve el usuario (empleado) que ejecutó el cambio.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}