<?php

namespace App\Models;

use App\Enums\VacanteEstado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa un asiento o plaza presupuestal viva (Token de nivel 3).
 */
class Vacante extends Model
{
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'vacantes';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'detalle_requisicion_id',
        'aspirante_id',
        'fecha_limite_anexo_a',
        'sueldo_anexo_d',
        'observaciones_rechazo',
        'fecha_alta_imss',
        'estado',
    ];

    /**
     * Retorna el tipado automático de los campos de la tabla.
     */
    protected function casts(): array
    {
        return [
            'fecha_limite_anexo_a' => 'datetime',
            'sueldo_anexo_d' => 'decimal:2',
            'fecha_alta_imss' => 'date',
            'estado' => VacanteEstado::class,
        ];
    }

    /**
     * Retorna la partida presupuestal o detalle a la que pertenece esta vacante.
     */
    public function detalleRequisicion(): BelongsTo
    {
        return $this->belongsTo(DetalleRequisicion::class, 'detalle_requisicion_id');
    }

    /**
     * Retorna el aspirante que ha ganado esta vacante y la cubre oficialmente (Anexo B).
     */
    public function aspirante(): BelongsTo
    {
        return $this->belongsTo(Aspirante::class, 'aspirante_id');
    }
}