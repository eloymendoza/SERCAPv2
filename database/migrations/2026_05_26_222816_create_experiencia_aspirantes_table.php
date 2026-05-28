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
        Schema::create('experiencia_aspirantes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('aspirante_id');
            $table->string('cargo', 150);
            $table->string('nombre_empresa', 150);
            $table->boolean('trabajo_actual')->default(false);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('responsabilidades')->nullable();

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
        Schema::dropIfExists('experiencia_aspirantes');
    }
};
