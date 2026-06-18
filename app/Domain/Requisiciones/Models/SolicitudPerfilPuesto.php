<?php

namespace App\Domain\Requisiciones\Models;

use App\Traits\GeneratesFolio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

/**
 * Representa la solicitud de autorización para la creación o modificación del perfil de un puesto.
 */
class SolicitudPerfilPuesto extends Model
{
    use SoftDeletes, GeneratesFolio;
    
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'solicitud_perfil_puestos';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'solicitante_id',
        'id_instancia_workflow',
        'direccion_id',
        'estado',
        'observaciones',
    ];

    /**
     * Retorna los perfiles de puesto asociados a esta solicitud.
     */
    public function perfiles(): HasMany
    {
        return $this->hasMany(PerfilPuesto::class, 'solicitud_id');
    }

    /**
     * Recupera la dirección organizativa solicitante.
     */
    public function direccion(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizativa::class, 'direccion_id');
    }

    /**
     * Orquesta la asignación de folio consecutivo para esta solicitud.
     */
    public function asignarFoliosDefinitivos(): void
    {
        if (empty($this->folio)) {
            $abreviatura = $this->direccion?->abreviatura ?? 'XX';
            $this->folio = $this->generarFolioConsecutivo('SPP', $abreviatura);
        }
    }
}