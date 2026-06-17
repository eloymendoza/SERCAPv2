<?php

namespace App\App\Console\Commands;

use App\Domain\Requisiciones\Models\DetalleRequisicion;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class TruncateRequisicionesCommand extends Command
{
    protected $signature = 'truncate:requisiciones';

    protected $description = 'Limpia y reinicia los IDs de las tablas de Requisiciones.';

    /**
     * Limpia registros y reinicia contadores Identity en SQL Server.
     * 
     * Nota: Emplea DELETE en lugar de TRUNCATE debido a las restricciones de llave
     * foránea, seguido de DBCC CHECKIDENT para inicializar el contador en cero.
     */
    public function handle(): int
    {
        $this->info('Iniciando limpieza de tablas de Requisiciones...');

        try {
            DB::beginTransaction();

            $models = [
                new DetalleRequisicion(),
                new Requisicion(),
                new SolicitudRequisicion(),
            ];

            foreach ($models as $model) {
                $table = $model->getTable();
                
                DB::table($table)->delete();
                DB::statement("DBCC CHECKIDENT ('{$table}', RESEED, 0)");
                
                $this->line("Tabla {$table} reiniciada.");
            }

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