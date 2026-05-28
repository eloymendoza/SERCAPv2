<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catalogo_idiomas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('codigo_iso', 5)->unique();
            $table->timestamps(7);
        });

        DB::table('catalogo_idiomas')->insert([
            ['nombre' => 'Español',   'codigo_iso' => 'es'],
            ['nombre' => 'Inglés',    'codigo_iso' => 'en'],
            ['nombre' => 'Francés',   'codigo_iso' => 'fr'],
            ['nombre' => 'Alemán',    'codigo_iso' => 'de'],
            ['nombre' => 'Portugués', 'codigo_iso' => 'pt'],
            ['nombre' => 'Italiano',  'codigo_iso' => 'it'],
            ['nombre' => 'Mandarín',  'codigo_iso' => 'zh'],
            ['nombre' => 'Japonés',   'codigo_iso' => 'ja'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_idiomas');
    }
};
