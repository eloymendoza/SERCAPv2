<?php

namespace App\Domain\Requisiciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstado;

/**
 * Representa la solicitud formal de presupuesto y personal de un proyecto (Workflow de entrada).
 */
class SolicitudRequisicion extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'solicitud_requisiciones';

    /**
     * Los valores por defecto para los atributos del modelo.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'estado' => 'borrador',
    ];

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'folio',
        'proyecto_id',
        'id_instancia_workflow',
        'solicitante_id',
        'direccion_id',
        'gerencia_id',
        'coordinacion_id',
        'observaciones',
        'estado',
    ];

    /**
     * Inicializa los eventos del modelo para la generación automática del folio.
     */
    protected static function booted(): void
    {
        static::creating(function (SolicitudRequisicion $model) {
            if (empty($model->folio) && $model->estado !== SolicitudRequisicionEstado::BORRADOR) {
                $year = date('Y');
                $random = mt_rand(10000, 99999);
                $model->folio = sprintf('SR-%s-%05d', $year, $random);
            }
        });

        static::updating(function (SolicitudRequisicion $model) {
            if (empty($model->folio) && $model->estado !== SolicitudRequisicionEstado::BORRADOR) {
                $year = date('Y');
                $random = mt_rand(10000, 99999);
                $model->folio = sprintf('SR-%s-%05d', $year, $random);
            }
        });
    }

    /**
     * Retorna el tipado automático de los campos de la tabla.
     */
    protected function casts(): array
    {
        return [
            'estado' => SolicitudRequisicionEstado::class,
        ];
    }

    /**
     * Retorna la requisición originada a partir de esta solicitud.
     */
    public function requisicion(): HasOne
    {
        return $this->hasOne(Requisicion::class, 'solicitud_id');
    }
}