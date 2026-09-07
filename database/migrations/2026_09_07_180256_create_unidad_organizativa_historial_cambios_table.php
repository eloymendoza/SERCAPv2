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
        Schema::create('unidad_organizativa_historial_cambios', function (Blueprint $table) {
            $table->id();
            
            // 1. Identificador de la Entidad
            $table->foreignId('unidad_organizativa_id')
                  ->constrained('unidades_organizativas')
                  ->cascadeOnDelete();
            
            // 2. Acción realizada ('created', 'updated', 'deleted', 'restored')
            $table->string('event');
            
            // 3. Delta de Datos
            $table->json('old_values')->nullable(); // Nulo si es un evento 'created'
            $table->json('new_values')->nullable(); // Nulo si es un evento 'deleted'
            
            // 4. Trazabilidad de Autoría
            $table->string('username')->nullable(); // Usuario que ejecutó el cambio
            $table->foreign('username')->references('username')->on('users')->nullOnDelete();
            
            // 5. Fecha exacta
            $table->timestamp('created_at')->useCurrent();
            
            // Índices recomendados
            $table->index(['unidad_organizativa_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidad_organizativa_historial_cambios');
    }
};