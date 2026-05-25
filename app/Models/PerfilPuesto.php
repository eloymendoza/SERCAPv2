<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa la versión formal del perfil de un puesto (Anexo A).
 */
class PerfilPuesto extends Model
{
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'perfil_puestos';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'puesto_id',
        'id_documento',
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
        'fecha_autorizacion',
        'estado',
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
            'fecha_autorizacion' => 'datetime',
        ];
    }

    /**
     * Retorna el puesto al cual pertenece este perfil.
     */
    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }
}