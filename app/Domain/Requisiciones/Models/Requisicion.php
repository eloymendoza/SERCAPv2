<?php

namespace App\Domain\Requisiciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Requisiciones\Enums\RequisicionEstado;

/**
 * Representa la requisición autorizada (Folio Padre) que agrupa las vacantes operativas.
 */
class Requisicion extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'requisiciones';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'solicitud_id',
        'folio',
        'tipo',
        'observaciones',
        'estado',
    ];

    /**
     * Retorna el tipado automático de los campos de la tabla.
     */
    protected function casts(): array
    {
        return [
            'estado' => RequisicionEstado::class,
        ];
    }

    /**
     * Retorna la solicitud original que dio origen a esta requisición.
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudRequisicion::class, 'solicitud_id');
    }

    /**
     * Retorna los desgloses o detalles de plazas solicitados en esta requisición.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleRequisicion::class, 'requisicion_id');
    }

    /**
     * Accessor dinámico: Calcula el total de vacantes agrupando los detalles de esta requisición.
     */
    public function getTotalVacantesAttribute(): int
    {
        return $this->detalles()->sum('cantidad_solicitada');
    }
}