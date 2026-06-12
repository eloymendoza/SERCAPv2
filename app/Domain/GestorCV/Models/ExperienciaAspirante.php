<?php

namespace App\Domain\GestorCV\Models;

use App\Domain\Requisiciones\Models\Aspirante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Representa la experiencia laboral de un aspirante.
 * Un aspirante puede tener muchas experiencias.
 */
class ExperienciaAspirante extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'experiencia_aspirantes';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'aspirante_id',
        'cargo',
        'nombre_empresa',
        'trabajo_actual',
        'fecha_inicio',
        'fecha_fin',
        'responsabilidades'
    ];

    /**
     * Retorna el aspirante al que pertenece esta experiencia mediante aspirante_id.
     */
    public function aspirante(): BelongsTo
    {
        return $this->belongsTo(Aspirante::class, 'aspirante_id');
    }
}