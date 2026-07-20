<?php

namespace App\Domain\Requisiciones\Models;

use App\Traits\GeneratesFolio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Requisiciones\Enums\RequisicionEstadoEnum;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstadoEnum;

/**
 * Representa la requisición autorizada (Folio Padre) que agrupa las vacantes operativas.
 */
class Requisicion extends Model
{
    use SoftDeletes, GeneratesFolio;
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
     * Asigna explícitamente el folio definitivo (Folio Padre) a la requisición.
     */
    public function asignarFolioDefinitivo(): void
    {
        if (empty($this->folio)) {
            $solicitud = $this->solicitud;
            $abreviaturaCompuesta = ($solicitud?->direccion?->abreviatura ?? '') . 
                                    ($solicitud?->gerencia?->abreviatura ?? '') . 
                                    ($solicitud?->coordinacion?->abreviatura ?? '');
            
            $this->folio = $this->generarFolioConsecutivo('P', $abreviaturaCompuesta, '-');
            $this->save();
        }
    }

    /**
     * Configura el tipado nativo de los atributos del modelo.
     */
    protected function casts(): array
    {
        return [
            'estado' => RequisicionEstadoEnum::class,
        ];
    }

    /**
     * Recupera la solicitud origen asociada a la requisición.
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudRequisicion::class, 'solicitud_id');
    }

    /**
     * Recupera el desglose de plazas operativas requeridas.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleRequisicion::class, 'requisicion_id');
    }

    /**
     * Calcula la sumatoria total de vacantes con base en los detalles registrados.
     */
    public function getTotalVacantesAttribute(): int
    {
        return $this->detalles()->sum('cantidad_solicitada');
    }
}