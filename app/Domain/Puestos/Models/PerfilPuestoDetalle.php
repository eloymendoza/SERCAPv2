<?php

namespace App\Domain\Puestos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa el contenido descriptivo y requerimientos de un perfil de puesto.
 */
class PerfilPuestoDetalle extends Model
{
    use SoftDeletes;
    
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'perfil_puesto_detalles';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'perfil_puesto_id',
        'nivel_organizacional',
        'identificacion',
        'revision',
        'mision_puesto',
        'funciones_responsabilidades',
        'relaciones_internas',
        'relaciones_externas',
        'autoridades_puesto',
        'manejo_recursos',
        'escolaridad_requerida',
        'otros_conocimientos',
        'experiencia_laboral',
        'herramientas_software',
        'idiomas',
        'competencias_organizacionales',
        'competencias_funcionales',
        'requiere_examen_vista',
        'requiere_examen_medico',
        'requiere_examen_psicometrico',
        'requiere_evaluacion_tecnica',
        'otros_examenes',
    ];

    /**
     * Retorna el tipado automático de los campos de la tabla.
     */
    protected function casts(): array
    {
        return [
            'manejo_recursos' => 'array',
            'competencias_organizacionales' => 'array',
            'competencias_funcionales' => 'array',
            'requiere_examen_vista' => 'boolean',
            'requiere_examen_medico' => 'boolean',
            'requiere_examen_psicometrico' => 'boolean',
            'requiere_evaluacion_tecnica' => 'boolean',
        ];
    }

    /**
     * Retorna el perfil padre.
     */
    public function perfil(): BelongsTo
    {
        return $this->belongsTo(PerfilPuesto::class, 'perfil_puesto_id');
    }
}