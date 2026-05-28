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
        Schema::create('conocimientos_tecnicos_aspirantes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('aspirante_id');
            $table->string('nombre', 100);
            $table->enum('categoria', ['lenguaje', 'framework', 'herramienta', 'metodologia', 'sin_clasificar']);
            
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
        Schema::dropIfExists('conocimientos_tecnicos_aspirantes');
    }
};
