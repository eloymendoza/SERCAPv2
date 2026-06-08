<?php

namespace App\Domain\Proyectos\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    /**
     * Define la conexión externa aislada para este catálogo.
     *
     * @var string
     */
    protected $connection = 'costos_contpaq';

    protected $table = 'proyecto';

    protected $primaryKey = 'idProyecto';

    /**
     * Asume ausencia de timestamps manejables por Laravel (catálogo de solo lectura).
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'proyecto',
        'descripcion',
        'lugar',
        'cliente',
        'jefeProyecto',
        'fechaInicio',
        'fechaTermino',
        'estado',
        'activoProyecto',
        'numeroProyectoSap',
        'sociedad',
        'gerenteProyecto',
    ];

    protected function casts(): array
    {
        return [
            'fechaInicio' => 'date',
            'fechaTermino' => 'date',
            'fechaFirmaContrato' => 'date',
            'fechaCreacion' => 'datetime',
            'activoProyecto' => 'boolean',
            'montoProyectoOriginal' => 'decimal:2',
            'montoProyectoPesos' => 'decimal:2',
        ];
    }
}