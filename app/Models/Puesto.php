<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Representa la estructura de puestos organizacional jerárquica y recursiva.
 */
class Puesto extends Model
{
    use SoftDeletes;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'puestos';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre_puesto',
        'direccion_id',
        'reporta_a_puesto_id',
        'tipo',
    ];

    /**
     * Retorna el puesto superior jerárquico al cual reporta.
     */
    public function reportaA(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'reporta_a_puesto_id');
    }

    /**
     * Retorna los puestos subordinados directos.
     */
    public function subordinados(): HasMany
    {
        return $this->hasMany(Puesto::class, 'reporta_a_puesto_id');
    }

    /**
     * Retorna las versiones históricas de perfil asociadas al puesto.
     */
    public function perfiles(): HasMany
    {
        return $this->hasMany(PerfilPuesto::class, 'puesto_id');
    }

    /**
     * Retorna los detalles de requisición asociados al puesto.
     */
    public function detallesRequisicion(): HasMany
    {
        return $this->hasMany(DetalleRequisicion::class, 'puesto_id');
    }
}