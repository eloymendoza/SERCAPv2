<?php

namespace App\Domain\Catalogos\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo Global de Tabulador Salarial.
 * Define los rangos económicos permitidos independientemente del puesto.
 */
class TabuladorSalario extends Model
{
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'tabulador_salario';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nivel_categoria',
        'sueldo_minimo',
        'sueldo_maximo',
        'estado',
    ];

    /**
     * Retorna el tipado automático de los campos de la tabla.
     */
    protected function casts(): array
    {
        return [
            'sueldo_minimo' => 'decimal:2',
            'sueldo_maximo' => 'decimal:2',
        ];
    }
}
