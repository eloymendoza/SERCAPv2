<?php

namespace App\Domain\Requisiciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'tipo_aspirante',
        'Id_personal',
        'ubicacion_id',
        'resumen',
        'estado_aspirante'
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

    /**
     * Retorna las experiencias laborales del aspirante.
     */
    public function experiencia(): HasMany
    {
        return $this->hasMany(ExperienciaAspirante::class, 'aspirante_id');
    }

    /**
     * Retorna la educación del aspirante.
     */
    public function educacion(): HasMany
    {
        return $this->hasMany(EducacionAspirante::class, 'aspirante_id');
    }

    /**
     * Retorna los certificados del aspirante.
     */
    public function certificado(): HasMany
    {
        return $this->hasMany(CertificadoAspirante::class, 'aspirante_id');
    }

    /**
     * Retorna los idiomas del aspirante.
     */
    public function idioma(): BelongsToMany
    {
        return $this->belongsToMany(
            CatalogoIdiomas::class,
            'idiomas_aspirantes',   // tabla pivote
            'aspirante_id',         // FK de este modelo en la pivote
            'idioma_id'             // FK del modelo relacionado en la pivote
        )->withPivot('nivel')       // exponer columnas extra de la pivote
         ->withTimestamps();
    }

    /**
     * Retorna los conocimientos técnicos del aspirante.
     */
    public function conocimientoTecnico(): HasMany
    {
        return $this->hasMany(ConocimientoTecnicoAspirante::class, 'aspirante_id');
    }
}