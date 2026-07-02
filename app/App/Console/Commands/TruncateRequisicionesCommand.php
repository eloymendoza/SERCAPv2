<?php

namespace App\App\Console\Commands;

use Throwable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\Models\PerfilPuesto;
use App\Domain\Puestos\Models\PerfilPuestoDetalle;
use App\Domain\Requisiciones\Models\Vacante;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\Models\Postulacion;
use App\Domain\Requisiciones\Models\DetalleRequisicion;
use App\Domain\Requisiciones\Models\PropuestaPuesto;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\Models\SolicitudPerfilPuesto;

class TruncateRequisicionesCommand extends Command
{
    protected $signature = 'truncate:requisiciones';

    protected $description = 'Limpia y reinicia los IDs de las tablas de Requisiciones, Perfiles y Puestos nuevos.';

    /**
     * Limpia registros y reinicia contadores Identity en SQL Server.
     * 
     * Nota: Emplea DELETE en lugar de TRUNCATE debido a las restricciones de llave
     * foránea, seguido de DBCC CHECKIDENT para inicializar el contador.
     */
    public function handle(): int
    {
        $this->info('Iniciando limpieza de tablas de Requisiciones y Perfiles...');

        try {
            DB::beginTransaction();

            $modelsToTruncate = [
                new Postulacion(),
                new PropuestaPuesto(),
                new Vacante(),
                new DetalleRequisicion(),
                new Requisicion(),
                new SolicitudRequisicion(),
                new PerfilPuestoDetalle(),
                new PerfilPuesto(),
                new SolicitudPerfilPuesto(),
            ];

            foreach ($modelsToTruncate as $model) {
                $table = $model->getTable();
                
                DB::table($table)->delete();
                DB::statement("DBCC CHECKIDENT ('{$table}', RESEED, 0)");
                
                $this->line("Tabla {$table} reiniciada.");
            }

            $puesto = new Puesto();
            $puestosTable = $puesto->getTable();
            
            DB::table($puestosTable)->where('id', '>', 720)->delete();
            
            $maxId = DB::table($puestosTable)->max('id') ?? 720;
            DB::statement("DBCC CHECKIDENT ('{$puestosTable}', RESEED, {$maxId})");
            
            $this->line("Tabla {$puestosTable} limpiada (IDs > 720 eliminados y contador ajustado).");

            DB::commit();
            $this->info('Limpieza completada.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('Fallo la operacion: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}