<?php

namespace App\Domain\EstructuraOrganizacional\Models;

use App\Domain\Autenticacion\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnidadOrganizativa extends Model
{
    use SoftDeletes;

    protected $table = 'unidades_organizativas';

    protected $fillable = [
        'parent_id',
        'nivel',
        'nombre',
        'abreviatura',
        'nombre_corto',
        'rfc',
        'encargado_id',
        'estado'
    ];



    /**
     * Resuelve la unidad jerárquica superior de la que depende operativamente.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Resuelve la colección de unidades que dependen estructuralmente de este nodo.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Vincula a la entidad lógica con el registro del empleado (dominio negocio).
     */
    public function encargado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encargado_id', 'id_personal');
    }
}