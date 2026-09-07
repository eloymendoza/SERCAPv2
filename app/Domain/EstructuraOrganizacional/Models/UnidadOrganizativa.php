<?php

namespace App\Domain\EstructuraOrganizacional\Models;

use App\Traits\AuditableModule;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Autenticacion\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnidadOrganizativa extends Model
{
    use SoftDeletes, AuditableModule;

    protected $table = 'unidades_organizativas';

    protected $dateFormat = 'Y-m-d\TH:i:s.v';

    protected $fillable = [
        'parent_id',
        'nivel',
        'nombre',
        'abreviatura',
        'nombre_corto',
        'rfc',
        'encargado_id',
        'encargado_usuario',
        'enabled_at',
        'disabled_at',
        'reemplaza_a_id',
        'estado'
    ];

    protected $casts = [
        'enabled_at' => 'date',
        'disabled_at' => 'date',
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
        return $this->belongsTo(User::class, 'encargado_usuario', 'username');
    }

    /**
     * Resuelve la unidad (inactiva) a la que esta unidad sustituyó.
     */
    public function reemplazaA(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reemplaza_a_id');
    }

    /**
     * Resuelve la unidad (activa) que sustituyó a esta unidad.
     */
    public function reemplazadoPor(): HasOne
    {
        return $this->hasOne(self::class, 'reemplaza_a_id');
    }

    /**
     * Define el modelo responsable de registrar el historial de cambios.
     */
    public function getHistoryModelFqcn(): string
    {
        return UnidadOrganizativaHistorialCambio::class;
    }

    /**
     * Define la llave foránea explícita, ya que Str::singular falla con plurables en español.
     */
    public function getHistoryForeignKey(): string
    {
        return 'unidad_organizativa_id';
    }
}