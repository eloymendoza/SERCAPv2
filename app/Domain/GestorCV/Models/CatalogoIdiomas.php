<?php

namespace App\Domain\GestorCV\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representa los idiomas disponibles para asignárselos a un aspirante.
 */
class CatalogoIdiomas extends Model
{
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'catalogo_idiomas';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'codigo_iso'
    ];
}