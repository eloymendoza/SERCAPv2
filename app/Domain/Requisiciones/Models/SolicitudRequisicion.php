<?php

namespace App\Domain\Requisiciones\Models;

use App\Traits\GeneratesFolio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Workflows\Contracts\Workflowable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstado;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;


/**
 * Representa la solicitud formal de presupuesto y personal de un proyecto (Workflow de entrada).
 */
class SolicitudRequisicion extends Model implements Workflowable
{
    use SoftDeletes, GeneratesFolio;
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'solicitud_requisiciones';

    /**
     * Los valores por defecto para los atributos del modelo.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'estado' => 'borrador',
    ];

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'folio',
        'proyecto_id',
        'id_instancia_workflow',
        'solicitante_id',
        'elaborador_id',
        'direccion_id',
        'gerencia_id',
        'coordinacion_id',
        'observaciones',
        'estado',
    ];



    /**
     * Configura el tipado nativo de los atributos del modelo.
     */
    protected function casts(): array
    {
        return [
            'estado' => SolicitudRequisicionEstado::class,
        ];
    }

    /**
     * Recupera la requisición dependiente generada a partir de esta solicitud.
     */
    public function requisicion(): HasOne
    {
        return $this->hasOne(Requisicion::class, 'solicitud_id');
    }

    /**
     * Recupera la dirección organizativa solicitante.
     */
    public function direccion(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizativa::class, 'direccion_id');
    }

    /**
     * Recupera la gerencia organizativa solicitante.
     */
    public function gerencia(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizativa::class, 'gerencia_id');
    }

    /**
     * Recupera la coordinación organizativa solicitante.
     */
    public function coordinacion(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizativa::class, 'coordinacion_id');
    }

    // --- Implementación de Workflowable ---

    /**
     * Provee el identificador principal requerido por el contrato Workflowable.
     */
    public function getIdentificador(): int
    {
        return $this->id;
    }

    /**
     * Vincula el identificador del motor de workflow y transiciona el estado a en proceso.
     */
    public function aplicarWorkflowInstancia(int $idInstanciaWorkflow): void
    {
        $this->asignarFoliosDefinitivos();

        $this->update([
            'id_instancia_workflow' => $idInstanciaWorkflow,
            'estado' => SolicitudRequisicionEstado::EN_PROCESO,
        ]);
    }

    /**
     * Finaliza la solicitud aprobándola automáticamente ante la ausencia de firmantes obligatorios.
     */
    public function autoAprobar(): void
    {
        $this->asignarFoliosDefinitivos();

        $this->update(['estado' => SolicitudRequisicionEstado::TERMINADO]);
    }

    /**
     * Orquesta la asignación de folios consecutivos una vez que la solicitud es emitida.
     */
    private function asignarFoliosDefinitivos(): void
    {
        if (empty($this->folio)) {
            $abreviatura = $this->direccion?->abreviatura ?? 'XX';
            $this->folio = $this->generarFolioConsecutivo('RP', $abreviatura);
        }

        if ($this->requisicion && empty($this->requisicion->folio)) {
            $this->requisicion->asignarFolioDefinitivo();
        }
    }

    /**
     * Retorna el identificador de la instancia de workflow asociada.
     */
    public function getIdentificadorInstancia(): int
    {
        return $this->id_instancia_workflow;
    }

    /**
     * Sincroniza el estado local del modelo basado en el estado devuelto por Django.
     */
    public function sincronizarEstadoWorkflow(string $estadoDjango): void
    {
        $estadoLocal = match (strtolower($estadoDjango)) {
            'terminado' => SolicitudRequisicionEstado::TERMINADO,
            'rechazado' => SolicitudRequisicionEstado::RECHAZADO,
            'cancelado' => SolicitudRequisicionEstado::CANCELADO,
            default     => SolicitudRequisicionEstado::EN_PROCESO,
        };

        $this->update(['estado' => $estadoLocal]);
    }
}