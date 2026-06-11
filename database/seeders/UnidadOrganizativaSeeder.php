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
        // Purga integral del catálogo previa a la resiembra
        DB::table('unidades_organizativas')->update(['parent_id' => null]);
        DB::table('unidades_organizativas')->delete();

        DB::transaction(function () {
            $estructura = [
                'ADMINISTRACIÓN Y FINANZAS' => [
                    'abreviatura' => 'AF',
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
                    'abreviatura' => 'CA',
                    'areas_directas' => [
                        'CALIDAD, AMBIENTAL, SEGURIDAD Y SALUD',
                        'CONTROL DE CALIDAD',
                        'CONTROL DE DOCUMENTOS Y REGISTROS',
                        'PROGRAMA DE GARANTÍA DE CALIDAD',
                        'SEGURIDAD INDUSTRIAL, PROTECCIÓN AMBIENTAL Y RADIOLÓGICA',
                    ],
                ],
                'CONSTRUCCIÓN Y MANTENIMIENTO' => [
                    'abreviatura' => 'CM',
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
                'DIRECCIÓN GENERAL' => [
                    'abreviatura' => 'DG',
                ],
                'INNOVACIÓN EN INGENIERÍA' => [
                    'abreviatura' => 'II',
                ],
            ];

            foreach ($estructura as $direccionNombre => $datosDireccion) {
                $direccion = UnidadOrganizativa::updateOrCreate(
                    ['nombre' => $direccionNombre, 'nivel' => 'direccion'],
                    [
                        'estado' => 'Activo',
                        'abreviatura' => $datosDireccion['abreviatura'] ?? null
                    ]
                );

                if (isset($datosDireccion['gerencias'])) {
                    foreach ($datosDireccion['gerencias'] as $gerenciaNombre => $areas) {
                        $gerencia = UnidadOrganizativa::firstOrCreate(
                            ['nombre' => $gerenciaNombre, 'parent_id' => $direccion->id, 'nivel' => 'gerencia'],
                            ['estado' => 'Activo']
                        );

                        foreach ($areas as $areaNombre) {
                            UnidadOrganizativa::firstOrCreate(
                                ['nombre' => $areaNombre, 'parent_id' => $gerencia->id, 'nivel' => 'area'],
                                ['estado' => 'Activo']
                            );
                        }
                    }
                }

                if (isset($datosDireccion['areas_directas'])) {
                    foreach ($datosDireccion['areas_directas'] as $areaNombre) {
                        UnidadOrganizativa::firstOrCreate(
                            ['nombre' => $areaNombre, 'parent_id' => $direccion->id, 'nivel' => 'area'],
                            ['estado' => 'Activo']
                        );
                    }
                }
            }
        });
    }
}