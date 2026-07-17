<?php

namespace App\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Domain\Puestos\Models\Puesto;

class MigratePuestosCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'migrate:puestos';

    /**
     * @var string
     */
    protected $description = 'ETL: Migra TCategorias desde SERCAPv1 hacia la tabla puestos';

    /**
     * Ejecuta la migración de puestos.
     */
    public function handle(): int
    {
        $this->info('Conectando con SERCAPv1.dbo.TCategorias...');

        $categorias = DB::connection('SERCAPv1')->table('TCategorias')->get();

        if ($categorias->isEmpty()) {
            $this->warn('La tabla de origen está vacía o no existe.');
            return self::FAILURE;
        }

        $total = $categorias->count();
        $this->info("Se encontraron {$total} registros. Iniciando migración...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $migrados = 0;

        DB::transaction(function () use ($categorias, &$migrados, $bar) {
            DB::unprepared('SET IDENTITY_INSERT puestos ON');

            try {
                foreach ($categorias as $categoria) {
                    
                    $direccionId = (int) $categoria->Direccion_ct;

                    $tipoManoObra = match (trim($categoria->ManoObra_ct ?? '')) {
                        'I' => 'indirecto',
                        'D' => 'directo',
                        default => 'directo',
                    };

                    $estado = in_array($direccionId, [186, 187, 188, 189, 190], true) ? 'activo' : 'legado';

                    $exists = DB::table('puestos')->where('id', $categoria->Id_categoria)->exists();

                    if ($exists) {
                        DB::table('puestos')
                            ->where('id', $categoria->Id_categoria)
                            ->update([
                                'nombre_puesto' => trim($categoria->Nombre_ct),
                                'direccion_id' => $direccionId,
                                'tipo' => $tipoManoObra,
                                'estado' => $estado,
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table('puestos')->insert([
                            'id' => $categoria->Id_categoria,
                            'nombre_puesto' => trim($categoria->Nombre_ct),
                            'direccion_id' => $direccionId,
                            'tipo' => $tipoManoObra,
                            'estado' => $estado,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $migrados++;
                    $bar->advance();
                }
            } finally {
                DB::unprepared('SET IDENTITY_INSERT puestos OFF');
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Migración completada. Total migrados: {$migrados}");
        
        return self::SUCCESS;
    }
}