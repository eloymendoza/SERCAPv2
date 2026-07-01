<?php

namespace App\Domain\Puestos\Models;

use App\Domain\Requisiciones\Models\SolicitudPerfilPuesto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa la versión formal del perfil de un puesto (Anexo A).
 */
class PerfilPuesto extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'perfil_puestos';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'puesto_id',
        'solicitud_id',
        'id_documento',
        'fecha_autorizacion',
        'estado',
    ];

    /**
     * Retorna el tipado automático de los campos de la tabla.
     */
    protected function casts(): array
    {
        return [
            'fecha_autorizacion' => 'datetime',
        ];
    }

    /**
     * Retorna el puesto al cual pertenece este perfil.
     */
    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    /**
     * Retorna la solicitud que autoriza la creación o modificación de este perfil.
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudPerfilPuesto::class, 'solicitud_id');
    }

    /**
     * Retorna los detalles de contenido del perfil.
     */
    public function detalle(): HasOne
    {
        return $this->hasOne(PerfilPuestoDetalle::class, 'perfil_puesto_id');
    }
}