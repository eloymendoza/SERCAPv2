<?php

namespace App\Domain\Requisiciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa la solicitud de autorización para la creación o modificación del perfil de un puesto.
 */
class SolicitudPerfilPuesto extends Model
{
    use SoftDeletes;
    
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'solicitud_perfil_puestos';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'solicitante_id',
        'id_instancia_workflow',
        'estado',
        'observaciones',
    ];

    /**
     * Retorna los perfiles de puesto asociados a esta solicitud.
     */
    public function perfiles(): HasMany
    {
        return $this->hasMany(PerfilPuesto::class, 'solicitud_id');
    }
}
