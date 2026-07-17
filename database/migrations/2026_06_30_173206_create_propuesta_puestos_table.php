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
        Schema::create('propuesta_puestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detalle_requisicion_id')->constrained('detalle_requisiciones')->onDelete('cascade');
            $table->string('nombre_puesto', 255);
            $table->unsignedBigInteger('reporta_a_puesto_id')->nullable();
            $table->string('tipo');
            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propuesta_puestos');
    }
};