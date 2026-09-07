<?php

namespace App\Domain\EstructuraOrganizacional\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JerarquiaHistorica extends Model
{
    protected $table = 'unidad_organizativa_jerarquia_historica';

    protected $fillable = [
        'padre_id',
        'hijo_id',
        'fecha_inicio',
        'fecha_fin',
        'usuario',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    /**
     * Obtiene la unidad organizativa padre en este periodo histórico.
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizativa::class, 'padre_id');
    }

    /**
     * Obtiene la unidad organizativa hija en este periodo histórico.
     */
    public function hijo(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizativa::class, 'hijo_id');
    }
}