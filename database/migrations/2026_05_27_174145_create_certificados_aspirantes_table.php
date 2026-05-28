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
        Schema::create('certificados_aspirantes', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('aspirante_id');
            $table->string('nombre');
            $table->string('institucion', 150)->nullable();
            // unsigned small int para no tener que asignar dia/mes inventados al ser date
            $table->unsignedSmallInteger('anio_fin')->nullable();
            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);

            $table->foreign('aspirante_id')
                ->references('id')
                ->on('aspirantes')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificados_aspirantes');
    }
};
