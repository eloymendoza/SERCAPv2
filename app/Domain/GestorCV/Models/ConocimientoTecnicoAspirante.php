<?php

namespace App\Domain\GestorCV\Models;

use App\Domain\Requisiciones\Models\Aspirante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Representa los conocimientos técnicos de un aspirante.
 * Un aspirante puede tener muchos conocimientos técnicos.
 */
class ConocimientoTecnicoAspirante extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'conocimientos_tecnicos_aspirantes';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'aspirante_id',
        'nombre',
        'categoria'
    ];

    /**
     * Retorna el aspirante al que pertenece este conocimiento técnico mediante aspirante_id.
     */
    public function aspirante(): BelongsTo
    {
        return $this->belongsTo(Aspirante::class, 'aspirante_id');
    }
}