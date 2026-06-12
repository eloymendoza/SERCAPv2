<?php

namespace App\Domain\Legacy\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Solo Lectura (Capa Anticorrupción)
 * Conecta con la base de datos SERCAPv1 para extraer datos legacy.
 */
class TPersonal extends Model
{
    protected $connection = 'SERCAPv1';
    protected $table = 'TPersonal';
    protected $primaryKey = 'Id_personal';
    public $timestamps = false;
    protected $keyType = 'int';
    protected $guarded = [];
}