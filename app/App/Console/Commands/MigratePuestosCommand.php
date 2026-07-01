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
        $omitidos = 0;

        DB::transaction(function () use ($categorias, &$migrados, &$omitidos, $bar) {
            foreach ($categorias as $categoria) {
                
                $direccionId = match ((int) $categoria->Direccion_ct) {
                    5 => 186,
                    26 => 187,
                    99 => 188,
                    70 => 189,
                    71 => 190,
                    default => $categoria->Direccion_ct >= 186 ? $categoria->Direccion_ct : null,
                };

                if ($direccionId === null) {
                    $omitidos++;
                    $bar->advance();
                    continue;
                }

                $tipoManoObra = match (trim($categoria->ManoObra_ct ?? '')) {
                    'I' => 'indirecto',
                    'D' => 'directo',
                    default => 'directo',
                };

                Puesto::updateOrCreate(
                    ['id' => $categoria->Id_categoria],
                    [
                        'nombre_puesto' => trim($categoria->Nombre_ct),
                        'direccion_id' => $direccionId,
                        'tipo' => $tipoManoObra,
                        'estado' => 'activo'
                    ]
                );

                $migrados++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Migración completada. Migrados: {$migrados} | Omitidos por regla de dirección: {$omitidos}");
        
        return self::SUCCESS;
    }
}