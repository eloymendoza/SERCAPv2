<?php

namespace App\Models;

use App\Domain\Requisiciones\Models\Aspirante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Representa la formación educativa de un aspirante.
 * Un aspirante puede tener muchas formaciones.
 */
class EducacionAspirante extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'educacion_aspirantes';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'aspirante_id',
        'nivel_estudio_id',
        'institucion',
        'titulo',
        'anio_fin',
        'estado_educacion'
    ];

    /**
     * Retorna el aspirante al que pertenece esta educacion mediante aspirante_id.
     */
    public function aspirante(): BelongsTo
    {
        return $this->belongsTo(Aspirante::class, 'aspirante_id');
    }
}