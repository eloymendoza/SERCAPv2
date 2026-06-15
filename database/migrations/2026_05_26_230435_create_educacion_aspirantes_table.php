<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('educacion_aspirantes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('aspirante_id');
            $table->string('institucion', 200);
            $table->unsignedBigInteger('nivel_estudio_id');
            $table->string('titulo', 200);

            // unsigned small int para no tener que asignar dia/mes inventados al ser date
            $table->unsignedSmallInteger('anio_fin')->nullable();

            $table->string('estado_educacion');

            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);

            $table->foreign('aspirante_id')
                ->references('id')->on('aspirantes')
                ->cascadeOnDelete();

            $table->foreign('nivel_estudio_id')
                ->references('id')->on('catalogo_nivel_estudios')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educacion_aspirantes');
    }
};
