<?php

namespace App\Domain\Requisiciones\Models;

use App\Domain\Puestos\Models\Puesto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa la propuesta temporal de un nuevo puesto solicitado en una requisición.
 */
class PropuestaPuesto extends Model
{
    use SoftDeletes;
    
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'propuesta_puestos';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'detalle_requisicion_id',
        'nombre_puesto',
        'reporta_a_puesto_id',
        'tipo',
    ];

    /**
     * Retorna el detalle de requisición asociado.
     */
    public function detalleRequisicion(): BelongsTo
    {
        return $this->belongsTo(DetalleRequisicion::class, 'detalle_requisicion_id');
    }

    /**
     * Retorna el puesto al que reportaría la propuesta.
     */
    public function reportaA(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'reporta_a_puesto_id');
    }
}