<?php

namespace App\Models;

use App\Enums\SolicitudRequisicionEstado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Representa la solicitud formal de presupuesto y personal de un proyecto (Workflow de entrada).
 */
class SolicitudRequisicion extends Model
{
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'solicitud_requisiciones';

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
        'direccion',
        'gerencia',
        'coordinacion',
        'observaciones',
        'estado',
    ];

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