<?php

namespace Database\Seeders;

use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadOrganizativaSeeder extends Seeder
{
    /**
     * Sincroniza la estructura organizativa inicial en el sistema.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $estructura = [
                'ADMINISTRACIÓN Y FINANZAS' => [
                    'gerencias' => [
                        'FINANZAS' => [
                            'CUENTAS POR COBRAR',
                            'CUENTAS POR PAGAR',
                            'CUMPLIMIENTO FISCAL Y ADMINISTRATIVO',
                            'NÓMINAS, VIÁTICOS Y CAJAS CHICAS',
                            'TESORERÍA',
                        ],
                        'TALENTO Y CULTURA ORGANIZACIONAL' => [
                            'ACCESO DE PERSONAL',
                            'NÓMINAS Y CUMPLIMIENTO LABORAL',
                            'RECLUTAMIENTO Y SELECCIÓN',
                            'TALENTO',
                        ],
                    ],
                    'areas_directas' => [
                        'SERVICIOS E INFRAESTRUCTURA',
                    ],
                ],
                'CALIDAD, AMBIENTAL, SEGURIDAD Y SALUD' => [
                    'areas_directas' => [
                        'CALIDAD, AMBIENTAL, SEGURIDAD Y SALUD',
                        'CONTROL DE CALIDAD',
                        'CONTROL DE DOCUMENTOS Y REGISTROS',
                        'PROGRAMA DE GARANTÍA DE CALIDAD',
                        'SEGURIDAD INDUSTRIAL, PROTECCIÓN AMBIENTAL Y RADIOLÓGICA',
                    ],
                ],
                'CONSTRUCCIÓN Y MANTENIMIENTO' => [
                    'gerencias' => [
                        'TÉCNICO' => [],
                    ],
                    'areas_directas' => [
                        'ALMACÉN',
                        'COMERCIALIZACIÓN',
                        'OFERTAS',
                        'SEGUIMIENTO Y CONTROL',
                        'SOPORTE ADMINISTRATIVO',
                        'VEHÍCULOS Y MAQUINARIA',
                    ],
                ],
                'DIRECTOR GENERAL' => [],
                'INNOVACIÓN EN INGENIERÍA' => [],
            ];

            foreach ($estructura as $direccionNombre => $datosDireccion) {
                $direccion = UnidadOrganizativa::firstOrCreate(
                    ['nombre' => $direccionNombre, 'nivel' => 1],
                    ['estado' => true]
                );

                if (isset($datosDireccion['gerencias'])) {
                    foreach ($datosDireccion['gerencias'] as $gerenciaNombre => $areas) {
                        $gerencia = UnidadOrganizativa::firstOrCreate(
                            ['nombre' => $gerenciaNombre, 'parent_id' => $direccion->id, 'nivel' => 2],
                            ['estado' => true]
                        );

                        foreach ($areas as $areaNombre) {
                            UnidadOrganizativa::firstOrCreate(
                                ['nombre' => $areaNombre, 'parent_id' => $gerencia->id, 'nivel' => 3],
                                ['estado' => true]
                            );
                        }
                    }
                }

                if (isset($datosDireccion['areas_directas'])) {
                    foreach ($datosDireccion['areas_directas'] as $areaNombre) {
                        UnidadOrganizativa::firstOrCreate(
                            ['nombre' => $areaNombre, 'parent_id' => $direccion->id, 'nivel' => 3],
                            ['estado' => true]
                        );
                    }
                }
            }
        });
    }
}
