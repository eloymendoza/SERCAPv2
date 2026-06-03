<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Representa los certificados de un aspirante.
 * Un aspirante puede tener muchos certificados.
 */
class CertificadoAspirante extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'certificados_aspirantes';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'aspirante_id',
        'nombre',
        'institucion',
        'anio_fin'
    ];

    /**
     * Retorna el aspirante al que pertenece este certificado mediante aspirante_id.
     */
    public function aspirante(): BelongsTo
    {
        return $this->belongsTo(Aspirante::class, 'aspirante_id');
    }
}