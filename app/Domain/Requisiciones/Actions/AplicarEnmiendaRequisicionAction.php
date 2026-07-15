<?php

namespace App\Domain\Requisiciones\Actions;

use Illuminate\Support\Facades\DB;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\Models\PerfilPuesto;
use App\Domain\Requisiciones\Models\Vacante;
use App\Exceptions\Domain\BusinessRuleException;
use App\Domain\Requisiciones\Enums\VacanteEstado;
use App\Domain\Requisiciones\Models\DetalleRequisicion;
use App\Domain\Requisiciones\Models\SolicitudPerfilPuesto;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;

/**
 * Orquesta la lógica de Enmiendas aplicando reducciones, cambios de perfil y ampliaciones.
 */
class AplicarEnmiendaRequisicionAction
{
    /**
     * Aplica los cambios de la solicitud de enmienda a su requisición padre.
     */
    public function execute(SolicitudRequisicion $solicitud): void
    {
        if (!$solicitud->esEnmienda()) {
            throw new \InvalidArgumentException('La solicitud provista no es una enmienda.');
        }

        $requisicionPadre = $solicitud->requisicionPadre;
        $requisicionPayload = $solicitud->requisicion; // La requisición temporal que trae los nuevos detalles propuestos

        if (!$requisicionPadre || !$requisicionPayload) {
            throw BusinessRuleException::withMessage('Faltan datos estructurales para procesar la enmienda.');
        }

        DB::transaction(function () use ($solicitud, $requisicionPadre, $requisicionPayload) {
            
            $detallesActuales = $requisicionPadre->detalles->keyBy('puesto_id');
            $detallesNuevos = $requisicionPayload->detalles;

            // 1. Procesar Reducciones y Cambios (solo puestos existentes en el padre)
            foreach ($detallesActuales as $puestoId => $detalleActual) {
                $detalleNuevo = $detallesNuevos->firstWhere('puesto_id', $puestoId);

                if (!$detalleNuevo) {
                    // El puesto fue eliminado completamente en la enmienda
                    $this->cancelarVacantesActivas($detalleActual, $detalleActual->cantidad_solicitada, "Puesto eliminado por Enmienda {$solicitud->folio}");
                    continue;
                }

                // Si se redujo la cantidad
                if ($detalleNuevo->cantidad_solicitada < $detalleActual->cantidad_solicitada) {
                    $diferencia = $detalleActual->cantidad_solicitada - $detalleNuevo->cantidad_solicitada;
                    $this->cancelarVacantesActivas($detalleActual, $diferencia, "Reducción de plantilla por Enmienda {$solicitud->folio}");
                    
                    // Actualizar el detalle padre
                    $detalleActual->update(['cantidad_solicitada' => $detalleNuevo->cantidad_solicitada]);
                }

                // Si se aumentó la cantidad (Ampliación sobre el mismo puesto)
                if ($detalleNuevo->cantidad_solicitada > $detalleActual->cantidad_solicitada) {
                    $diferencia = $detalleNuevo->cantidad_solicitada - $detalleActual->cantidad_solicitada;
                    $this->crearNuevasVacantes($detalleActual, $diferencia);
                    
                    // Actualizar el detalle padre
                    $detalleActual->update(['cantidad_solicitada' => $detalleNuevo->cantidad_solicitada]);
                }

                // Cambio de perfil (Sueldo o condiciones que requieran reiniciar SLA)
                if ((float)$detalleNuevo->sueldo_asignado !== (float)$detalleActual->sueldo_asignado) {
                    $vacantesLibres = $detalleActual->vacantes()
                        ->whereIn('estado', [VacanteEstado::BUSQUEDA_ACTIVA, VacanteEstado::PENDIENTE_PERFIL])
                        ->count();

                    if ($vacantesLibres > 0) {
                        $this->cancelarVacantesActivas($detalleActual, $vacantesLibres, "Cambio estructural (sueldo) por Enmienda {$solicitud->folio}");
                    }
                    
                    // Actualizamos el sueldo en el padre
                    $detalleActual->update(['sueldo_asignado' => $detalleNuevo->sueldo_asignado]);
                    
                    if ($vacantesLibres > 0) {
                        // Creamos nuevas vacantes para reiniciar el KPI
                        $this->crearNuevasVacantes($detalleActual, $vacantesLibres);
                    }
                }
            }

            // 2. Procesar Ampliaciones (Nuevos Puestos y Propuestas)
            foreach ($detallesNuevos as $detalleNuevo) {
                if ($detalleNuevo->puesto_id === null || !$detallesActuales->has($detalleNuevo->puesto_id)) {
                    
                    // Si es una propuesta, convertirla a puesto real
                    if ($detalleNuevo->propuestaPuesto) {
                        $propuesta = $detalleNuevo->propuestaPuesto;

                        $puestoNuevo = Puesto::create([
                            'nombre_puesto' => $propuesta->nombre_puesto,
                            'direccion_id' => $solicitud->direccion_id,
                            'reporta_a_puesto_id' => $propuesta->reporta_a_puesto_id,
                            'tipo' => $propuesta->tipo,
                        ]);

                        $solicitudPerfil = SolicitudPerfilPuesto::create([
                            'solicitante_id' => $solicitud->elaborador_id ?? $solicitud->solicitante_id,
                            'direccion_id' => $solicitud->direccion_id,
                            'estado' => 'borrador', 
                            'observaciones' => 'Solicitud autogenerada desde enmienda ' . $solicitud->folio,
                        ]);

                        PerfilPuesto::create([
                            'solicitud_id' => $solicitudPerfil->id,
                            'puesto_id' => $puestoNuevo->id,
                        ]);

                        $detalleNuevo->update(['puesto_id' => $puestoNuevo->id]);
                        $propuesta->delete();
                        $detalleNuevo->load('puesto'); // Refrescar para tener el modelo puesto instanciado
                    }

                    // Movemos el detalle a la requisicion padre
                    $detalleNuevo->update(['requisicion_id' => $requisicionPadre->id]);
                    $this->crearNuevasVacantes($detalleNuevo, $detalleNuevo->cantidad_solicitada);
                }
            }

            // 3. Eliminar la requisición temporal para mantener limpia la BD
            $requisicionPayload->detalles()->delete();
            $requisicionPayload->delete();
        });
    }

    /**
     * Cancela N vacantes activas de un detalle específico.
     */
    private function cancelarVacantesActivas(DetalleRequisicion $detalle, int $cantidadACancelar, string $motivo): void
    {
        $vacantesLibres = $detalle->vacantes()
            ->whereIn('estado', [VacanteEstado::BUSQUEDA_ACTIVA, VacanteEstado::PENDIENTE_PERFIL, VacanteEstado::PENDIENTE_VINCULACION_SGC])
            ->limit($cantidadACancelar)
            ->get();

        if ($vacantesLibres->count() < $cantidadACancelar) {
            throw BusinessRuleException::withMessage("No hay suficientes vacantes libres en el puesto {$detalle->puesto_id} para cancelar. Algunas ya están en proceso o contratadas.");
        }

        foreach ($vacantesLibres as $vacante) {
            $vacante->cancelar($motivo);
        }
    }

    private function crearNuevasVacantes(DetalleRequisicion $detalle, int $cantidadACrear): void
    {
        $puesto = $detalle->puesto;
        $estadoInicial = VacanteEstado::PENDIENTE_VINCULACION_SGC;

        if ($puesto) {
            if ($puesto->tienePerfilVinculadoSGC()) {
                $estadoInicial = VacanteEstado::BUSQUEDA_ACTIVA;
            } elseif ($puesto->tienePerfilLocalEnProceso()) {
                $estadoInicial = VacanteEstado::PENDIENTE_PERFIL;
            }
        }

        for ($i = 0; $i < $cantidadACrear; $i++) {
            Vacante::create([
                'detalle_requisicion_id' => $detalle->id,
                'estado' => $estadoInicial,
            ]);
        }
    }
}