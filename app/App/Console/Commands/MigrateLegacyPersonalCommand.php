<?php

namespace App\App\Console\Commands;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Domain\Legacy\Models\TPersonal;
use App\Domain\Requisiciones\Models\Aspirante;

class MigrateLegacyPersonalCommand extends Command
{
    protected $signature = 'migrate:legacy-personal';

    protected $description = 'Migra los datos del personal activo (TPersonal) desde V1 hacia Aspirantes en V2 (ETL Upsert)';

    public function handle()
    {
        $this->info('Iniciando proceso ETL: V1 (TPersonal) -> V2 (Aspirantes)');

        try {
            DB::connection('SERCAPv1')->getPdo();
        } catch (Throwable $e) {
            $this->error('No se pudo conectar a SERCAPv1. Verifica las credenciales en el .env');
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        $ids = [11062, 11018, 11172, 10970, 10510, 1476, 2235, 1509, 11055, 11094, 10850, 11021, 11081, 10962];
        $query = TPersonal::whereIn('Id_personal', $ids);
        $total = $query->count();
        $this->info("Total de registros a evaluar: {$total}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $procesados = 0;
        $errores = 0;

        $query->chunk(200, function ($chunk) use ($bar, &$procesados, &$errores) {
            foreach ($chunk as $personal) {
                try {
                    $email = !empty(trim($personal->email_ps)) ? substr(trim($personal->email_ps), 0, 100) : null;
                    $tel = trim($personal->celular_ps ?: $personal->telefono_ps);
                    $telefono = !empty($tel) ? substr($tel, 0, 15) : null;

                    $formatStr = function ($val) {
                        $val = trim($val ?? '');
                        return (empty($val) || in_array(strtoupper($val), ['SIN APELLIDO', 'SIN NOMBRE', 'N/A'])) 
                            ? null 
                            : substr(Str::title(mb_strtolower($val)), 0, 100);
                    };

                    $datos = [
                        'nombres'          => $formatStr($personal->Nombre_ps) ?? 'X',
                        'apellido_paterno' => $formatStr($personal->APaterno_ps) ?? 'X',
                        'apellido_materno' => $formatStr($personal->AMaterno_ps) ?? 'X',
                        'email'            => $email,
                        'telefono'         => $telefono,
                        'tipo_aspirante'   => 'personal_activo',
                        'estado_aspirante' => 'nuevo'
                    ];

                    $aspirante = Aspirante::find($personal->Id_personal);

                    if ($aspirante) {
                        $aspirante->update($datos);
                    } else {
                        $datos['id'] = $personal->Id_personal;
                        $datos['created_at'] = now();
                        $datos['updated_at'] = now();
                        
                        // Requiere habilitar IDENTITY_INSERT a nivel de sesión en SQL Server 
                        // para conservar la paridad de llaves primarias con el sistema legacy.
                        DB::unprepared('SET IDENTITY_INSERT aspirantes ON');
                        DB::table('aspirantes')->insert($datos);
                        DB::unprepared('SET IDENTITY_INSERT aspirantes OFF');
                    }

                    $procesados++;
                } catch (Throwable $e) {
                    $errores++;
                    $this->error("\nError procesando Id_personal {$personal->Id_personal}: " . $e->getMessage());
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        
        $this->info("Migración completada.");
        $this->line("Procesados con éxito: <info>{$procesados}</info>");
        if ($errores > 0) {
            $this->line("Con errores: <error>{$errores}</error>");
        }

        return Command::SUCCESS;
    }
}