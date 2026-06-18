<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Provee la funcionalidad para generar folios consecutivos por área y año.
 * 
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait GeneratesFolio
{
    /**
     * Genera un folio consecutivo con el formato: {Prefijo}{Abreviatura}{Año}{Separador}{Consecutivo}
     * Ejemplo: RPAF26001 o PAFTCRS26-001
     * 
     * @param string $prefijo Ej. RP, PP, o P
     * @param string $abreviatura Ej. AF o AFTCRS
     * @param string $separadorConsecutivo Opcional, ej. '-' para PAFTCRS26-001
     */
    protected function generarFolioConsecutivo(string $prefijo, string $abreviatura, string $separadorConsecutivo = ''): string
    {
        $year = date('y');
        $abrev = empty($abreviatura) ? 'XX' : $abreviatura;
        
        $baseFolio = "{$prefijo}{$abrev}{$year}{$separadorConsecutivo}";

        $consecutivo = DB::transaction(function () use ($baseFolio) {
            $secuencia = DB::table('folio_secuencias')
                ->where('base_folio', $baseFolio)
                ->lockForUpdate()
                ->first();

            if ($secuencia) {
                $nuevoConsecutivo = $secuencia->consecutivo + 1;
                
                DB::table('folio_secuencias')
                    ->where('base_folio', $baseFolio)
                    ->update(['consecutivo' => $nuevoConsecutivo]);

                return $nuevoConsecutivo;
            }

            try {
                DB::table('folio_secuencias')->insert([
                    'base_folio' => $baseFolio,
                    'consecutivo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                return 1;
            } catch (\Exception $e) {
                $secuencia = DB::table('folio_secuencias')
                    ->where('base_folio', $baseFolio)
                    ->lockForUpdate()
                    ->first();

                $nuevoConsecutivo = $secuencia->consecutivo + 1;

                DB::table('folio_secuencias')
                    ->where('base_folio', $baseFolio)
                    ->update(['consecutivo' => $nuevoConsecutivo]);

                return $nuevoConsecutivo;
            }
        });

        return sprintf("%s%03d", $baseFolio, $consecutivo);
    }
}