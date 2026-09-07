<?php

namespace App\App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use App\Domain\EstructuraOrganizacional\Models\JerarquiaHistorica;

class PopulateJerarquiaHistoricaCommand extends Command
{
    protected $signature = 'unidades:populate-historial';
    protected $description = 'Puebla la tabla de jerarquía histórica inicial basada en el estado actual de las unidades organizativas.';

    public function handle()
    {
        $this->info('Iniciando poblamiento de la jerarquía histórica...');
        
        $unidades = UnidadOrganizativa::whereNotNull('parent_id')->get();
        
        if ($unidades->isEmpty()) {
            $this->warn('No hay unidades con padre asignado para procesar.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($unidades->count());
        $bar->start();

        $registros = [];
        $now = now();
        
        foreach ($unidades as $unidad) {
            // Utilizamos la fecha de habilitación (o de creación en su defecto) como el inicio de la relación
            $fechaInicio = $unidad->enabled_at ? $unidad->enabled_at->startOfDay() : $unidad->created_at;
            
            $registros[] = [
                'padre_id' => $unidad->parent_id,
                'hijo_id' => $unidad->id,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => null,
                'usuario' => null, // Script del sistema
                'created_at' => $now,
                'updated_at' => $now,
            ];
            
            $bar->advance();
        }
        
        JerarquiaHistorica::insert($registros);
        
        $bar->finish();
        $this->newLine();
        $this->info('Poblamiento finalizado con éxito. Se insertaron ' . count($registros) . ' registros.');

        return self::SUCCESS;
    }
}