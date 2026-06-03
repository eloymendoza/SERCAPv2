<?php

namespace App\Domain\Requisiciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Requisiciones\Enums\PostulacionEstado;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa el viaje individual de un candidato compitiendo por una plaza.
 */
class Postulacion extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'postulaciones';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'aspirante_id',
        'detalle_requisicion_id',
        'tipo_movimiento',
        'fecha_inicio_sla_entrevista',
        'resultado_entrevista',
        'resultado_examen_tecnico',
        'resultado_medico',
        'resultado_psicometrico',
        'notas_autorizacion_excepcional',
        'estado',
    ];

    /**
     * Retorna el tipado automático de los campos de la tabla.
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio_sla_entrevista' => 'datetime',
            'estado' => PostulacionEstado::class,
        ];
    }

    /**
     * Retorna el aspirante o candidato que aplica mediante esta postulación.
     */
    public function aspirante(): BelongsTo
    {
        return $this->belongsTo(Aspirante::class, 'aspirante_id');
    }

    /**
     * Retorna la partida presupuestal por la que compite esta postulación.
     */
    public function detalleRequisicion(): BelongsTo
    {
        return $this->belongsTo(DetalleRequisicion::class, 'detalle_requisicion_id');
    }
}