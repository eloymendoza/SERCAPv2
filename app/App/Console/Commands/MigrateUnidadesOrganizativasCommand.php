<?php

namespace App\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateUnidadesOrganizativasCommand extends Command
{
    protected $signature = 'migrate:unidades-organizativas';

    protected $description = 'ETL: Migra datos jerárquicos desde la vista legacy vAreas hacia unidades_organizativas';

    public function handle()
    {
        $this->info('Conectando con bdidiai.dbo.vAreas...');

        // Query principal sin filtros para extraer la totalidad histórica
        $areas = DB::table('bdidiai.dbo.vAreas')
            ->orderBy('idArea') // Fundamental para insertar padres antes que hijos y evitar error FK
            ->get();

        if ($areas->isEmpty()) {
            $this->warn('No se encontraron registros en la vista de origen.');
            return self::FAILURE;
        }

        $total = $areas->count();
        $this->info("Registros extraídos: {$total}. Iniciando mapeo jerárquico...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $gerenciasSaltadas = [];
        $registrosAInsertar = [];
        $abreviaturasUsadas = [];

        // Fase 1: Análisis en memoria (Construcción del árbol)
        foreach ($areas as $row) {
            $nombre = trim($row->nombre);
            
            // 1. Descarte estricto de nodos inválidos ("N/A") en cualquier nivel
            if ($nombre === 'N/A' || $nombre === 'NA') {
                if ($row->idArea == $row->idGerencia) {
                    // Es una gerencia fantasma, guardamos registro para heredar hijos a la dirección
                    $gerenciasSaltadas[$row->idArea] = $row->idDireccion;
                }
                $bar->advance();
                continue; // Omitimos la inserción por completo
            }

            $nivel = '';
            $parentId = null;

            // 2. Determinación de Nivel Estricto y Asignación de Padre
            if ($row->idArea == $row->idDireccion && is_null($row->idGerencia) && is_null($row->idDepartamento)) {
                $nivel = 'direccion';
                $parentId = null;
            } elseif ($row->idArea == $row->idGerencia && is_null($row->idDepartamento)) {
                $nivel = 'gerencia';
                $parentId = $row->idDireccion;
            } else {
                $nivel = 'area'; // Cualquier hoja final es área
                // Si el padre inmediato fue una gerencia descartada, nos colgamos de la dirección
                $parentId = $gerenciasSaltadas[$row->idGerencia] ?? $row->idGerencia;
            }

            // 3. Asignación de encargados específicos
            $encargadoId = match ((int) $row->idArea) {
                186 => 1476,
                187 => 2059,
                188 => 3002,
                189 => 4908,
                190 => 6052,
                231 => 6197,
                232 => 11021,
                250 => 1133,
                default => null,
            };

            // 4. Generación y control de colisiones de Abreviatura
            $abreviatura = trim($row->abreviatura ?? '');
            
            if (empty($abreviatura)) {
                $baseAbrev = $this->generarIniciales($nombre);
            } else {
                $baseAbrev = mb_substr($abreviatura, 0, 2);
            }

            $abreviatura = $baseAbrev;
            $contador = 1;
            while (in_array($abreviatura, $abreviaturasUsadas, true)) {
                $primeraLetra = mb_substr($baseAbrev, 0, 1);
                if ($contador < 10) {
                    $abreviatura = $primeraLetra . $contador;
                } else {
                    $abreviatura = $primeraLetra . chr(55 + $contador); // 10 = A, 11 = B...
                }
                $contador++;
            }
            
            $abreviaturasUsadas[] = $abreviatura;

            $enRangoValido = ($row->idArea >= 186 && $row->idArea <= 190) || $row->idArea >= 231;

            if (!$enRangoValido || (int) $row->borrado === 1) {
                $estado = 'legado';
            } else {
                $estado = 'activo';
            }

            // 5. Armado de payload
            $registrosAInsertar[] = [
                'id'           => $row->idArea,
                'parent_id'    => $parentId,
                'nivel'        => $nivel,
                'nombre'       => $nombre,
                'abreviatura'  => $abreviatura,
                'nombre_corto' => $row->nombreCorto,
                'rfc'          => $row->rfc,
                'encargado_id' => $encargadoId,
                'estado'       => $estado, 
                'created_at'   => now()->format('Y-m-d\TH:i:s.v'), 
                'updated_at'   => now()->format('Y-m-d\TH:i:s.v'),
            ];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Ordenamiento topológico en memoria (padres primero) para evitar errores FK
        usort($registrosAInsertar, function ($a, $b) {
            $pesos = ['direccion' => 1, 'gerencia' => 2, 'area' => 3];
            return $pesos[$a['nivel']] <=> $pesos[$b['nivel']];
        });

        $totValidos = count($registrosAInsertar);
        $this->info("Filtrado completado. Se procesarán {$totValidos} nodos válidos.");
        $barInsert = $this->output->createProgressBar($totValidos);
        $barInsert->start();

        // Fase 2: Persistencia Física en BD con Identity Insert
        DB::unprepared('SET IDENTITY_INSERT unidades_organizativas ON');

        try {
            foreach ($registrosAInsertar as $data) {
                $exists = DB::table('unidades_organizativas')->where('id', $data['id'])->exists();

                if (!$exists) {
                    DB::table('unidades_organizativas')->insert($data);
                } else {
                    $updateData = $data;
                    unset($updateData['id']);
                    unset($updateData['created_at']);
                    
                    DB::table('unidades_organizativas')->where('id', $data['id'])->update($updateData);
                }
                $barInsert->advance();
            }
        } finally {
            DB::unprepared('SET IDENTITY_INSERT unidades_organizativas OFF');
        }

        $barInsert->finish();
        $this->newLine();
        $this->info('Migración jerárquica finalizada con éxito.');

        return self::SUCCESS;
    }

    /**
     * Extrae las iniciales del nombre ignorando conectores comunes.
     */
    private function generarIniciales(string $nombre): string
    {
        $palabras = explode(' ', mb_strtoupper(trim($nombre)));
        $omitir = ['DE', 'Y', 'LA', 'EL', 'LOS', 'LAS', 'EN', 'POR', 'CON', 'PARA', 'A', 'AL', 'DEL'];
        $iniciales = '';

        foreach ($palabras as $p) {
            $p = preg_replace('/[^A-ZÑÁÉÍÓÚ]/u', '', $p);
            if (!empty($p) && !in_array($p, $omitir)) {
                $iniciales .= mb_substr($p, 0, 1);
                if (mb_strlen($iniciales) === 2) {
                    break;
                }
            }
        }

        if (mb_strlen($iniciales) < 2 && mb_strlen($nombre) >= 2) {
            $p = preg_replace('/[^A-ZÑÁÉÍÓÚ]/u', '', mb_strtoupper(trim($nombre)));
            $iniciales = mb_substr($p, 0, 2);
        }

        $resultado = empty($iniciales) ? 'ND' : $iniciales;
        return mb_substr($resultado, 0, 2);
    }
}