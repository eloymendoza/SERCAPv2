<?php

namespace App\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateTabuladorCommand extends Command
{
    protected $signature = 'migrate:tabulador';

    protected $description = 'ETL: Migra TCategoriasTabular desde SERCAPv1 hacia tabulador_salario';

    public function handle()
    {
        $this->info('Conectando con SERCAPv1.dbo.TCategoriasTabular...');

        // Consultamos la tabla legacy
        $categorias = DB::connection('SERCAPv1')->table('TCategoriasTabular')->get();

        if ($categorias->isEmpty()) {
            $this->warn('La tabla de origen está vacía o no existe.');
            return self::FAILURE;
        }

        $total = $categorias->count();
        $this->info("Registros extraídos: {$total}. Iniciando volcado...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // Desactivamos chequeo de llave primaria (Identity) en SQL Server para conservar los IDs originales
        DB::unprepared('SET IDENTITY_INSERT tabulador_salario ON');

        try {
            foreach ($categorias as $cat) {
                $exists = DB::table('tabulador_salario')->where('id', $cat->Id_categoriatabular)->exists();

                if (!$exists) {
                    DB::table('tabulador_salario')->insert([
                        'id' => $cat->Id_categoriatabular,
                        'nivel_categoria' => trim($cat->Nombre_ct),
                        'sueldo_minimo' => 0.00, // No provisto en esquema legacy
                        'sueldo_maximo' => 0.00, // No provisto en esquema legacy
                        'estado' => 'activo',
                        'created_at' => now()->format('Y-m-d\TH:i:s.v'),
                        'updated_at' => now()->format('Y-m-d\TH:i:s.v'),
                    ]);
                } else {
                    DB::table('tabulador_salario')->where('id', $cat->Id_categoriatabular)->update([
                        'nivel_categoria' => trim($cat->Nombre_ct),
                        'updated_at' => now()->format('Y-m-d\TH:i:s.v'),
                    ]);
                }

                $bar->advance();
            }
        } finally {
            DB::unprepared('SET IDENTITY_INSERT tabulador_salario OFF');
        }

        $bar->finish();
        $this->newLine();
        $this->info('Migración de categorías tabulares finalizada con éxito.');

        return self::SUCCESS;
    }
}
