<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo que sirve para mostar las opciones de niveles de estudio que se pueden elegir para el aplicante. En caso de que se necesiten modificar, será directamente en la tabla
     */
    public function up(): void
    {
        Schema::create('catalogo_nivel_estudios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->timestamps(7);
        });

        DB::table('catalogo_nivel_estudios')->insert([
            ['nombre' => 'Primaria'],
            ['nombre' => 'Secundaria'],
            ['nombre' => 'Bachillerato'],
            ['nombre' => 'Carrera Técnica'],
            ['nombre' => 'Licenciatura'],
            ['nombre' => 'Especialidad'],
            ['nombre' => 'Maestría'],
            ['nombre' => 'Doctorado'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_nivel_estudios');
    }
};
