<?php

namespace App\Domain\Requisiciones\Models;

use App\Domain\Puestos\Models\Puesto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Requisiciones\Enums\TipoContrato;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa el desglose o partida específica de un puesto solicitado en una requisición.
 */
class DetalleRequisicion extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'detalle_requisiciones';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'requisicion_id',
        'puesto_id',
        'cantidad_solicitada',
        'disciplina_id',
        'tabulador_id',
        'sueldo_asignado',
        'turno_horas',
        'tipo_contrato',
        'fecha_inicio',
        'fecha_termino',
        'fecha_limite_requerimiento',
        'empleados_propuestos',
    ];

    /**
     * Retorna el tipado automático de los campos de la tabla.
     */
    protected function casts(): array
    {
        return [
            'sueldo_asignado' => 'decimal:2',
            'tipo_contrato' => TipoContrato::class,
            'fecha_inicio' => 'date',
            'fecha_termino' => 'date',
            'fecha_limite_requerimiento' => 'date',
        ];
    }

    /**
     * Retorna la requisición padre a la que pertenece este desglose.
     */
    public function requisicion(): BelongsTo
    {
        return $this->belongsTo(Requisicion::class, 'requisicion_id');
    }

    /**
     * Retorna el catálogo de puesto correspondiente a este desglose.
     */
    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    /**
     * Retorna la disciplina o área funcional asociada a este desglose.
     */
    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    /**
     * Retorna los asientos o vacantes físicas generadas para esta partida.
     */
    public function vacantes(): HasMany
    {
        return $this->hasMany(Vacante::class, 'detalle_requisicion_id');
    }

    /**
     * Retorna las postulaciones activas registradas para competir por esta partida.
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class, 'detalle_requisicion_id');
    }

    /**
     * Retorna la propuesta de puesto asociada a este detalle (si aplica).
     */
    public function propuestaPuesto(): HasOne
    {
        return $this->hasOne(PropuestaPuesto::class, 'detalle_requisicion_id');
    }
}