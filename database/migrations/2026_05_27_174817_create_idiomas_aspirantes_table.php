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
        Schema::create('idiomas_aspirantes', function (Blueprint $table) {
            // PK compuesta (idioma_id + aspirante_id)
            $table->unsignedBigInteger('idioma_id');
            $table->unsignedBigInteger('aspirante_id');
            $table->string('nivel');
 
            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);
 
            $table->primary(['idioma_id', 'aspirante_id']);
 
            $table->foreign('idioma_id')
                ->references('id')
                ->on('catalogo_idiomas');
 
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
        Schema::dropIfExists('idiomas_aspirantes');
    }
};
