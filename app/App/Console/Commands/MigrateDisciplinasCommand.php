<?php

namespace App\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateDisciplinasCommand extends Command
{
    protected $signature = 'migrate:disciplinas';

    protected $description = 'ETL: Migra datos del catálogo TAreas hacia la tabla de disciplinas';

    public function handle()
    {
        $this->info('Conectando con BitacorasElectronicasDev.dbo.TAreas...');

        // Consultamos excluyendo los registros con estado -1 (eliminados lógicos en origen)
        $areas = DB::table('BitacorasElectronicasDev.dbo.TAreas')
            ->where('status', '!=', -1)
            ->get();

        if ($areas->isEmpty()) {
            $this->warn('La tabla de origen está vacía o no existe.');
            return self::FAILURE;
        }

        $total = $areas->count();
        $this->info("Registros extraídos: {$total}. Iniciando volcado...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // Desactivamos temporalmente el chequeo de llave primaria (Identity) en SQL Server 
        // para garantizar que id_area se conserve idéntico en el destino.
        DB::unprepared('SET IDENTITY_INSERT disciplinas ON');

        try {
            foreach ($areas as $area) {
                // Se utiliza DB::table en lugar del ORM en el Insert para que respete el IDENTITY_INSERT
                $exists = DB::table('disciplinas')->where('id', $area->id_area)->exists();

                if (!$exists) {
                    DB::table('disciplinas')->insert([
                        'id' => $area->id_area,
                        'nombre' => $area->nombre_area,
                        'abreviatura' => $area->abreviatura,
                        'estado' => $area->status == 1 ? 'activo' : 'inactivo',
                        'created_at' => $area->created_at ?? now(),
                        'updated_at' => $area->updated_at ?? now(),
                    ]);
                } else {
                    DB::table('disciplinas')->where('id', $area->id_area)->update([
                        'nombre' => $area->nombre_area,
                        'abreviatura' => $area->abreviatura,
                        'estado' => $area->status == 1 ? 'activo' : 'inactivo',
                        'updated_at' => now(),
                    ]);
                }

                $bar->advance();
            }
        } finally {
            // Aseguramos restaurar el constraint pase lo que pase
            DB::unprepared('SET IDENTITY_INSERT disciplinas OFF');
        }

        $bar->finish();
        $this->newLine();
        $this->info('Migración del catálogo finalizada con éxito.');

        return self::SUCCESS;
    }
}