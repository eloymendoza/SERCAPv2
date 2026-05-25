<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Representa a un aspirante que compite en el embudo de reclutamiento.
 */
class Aspirante extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'aspirantes';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'telefono',
        'observaciones',
    ];

    /**
     * Retorna las postulaciones asociadas al aspirante.
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class, 'aspirante_id');
    }

    /**
     * Retorna la vacante (asiento) cubierta por el aspirante (Anexo B).
     */
    public function vacante(): HasOne
    {
        return $this->hasOne(Vacante::class, 'aspirante_id');
    }
}