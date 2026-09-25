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
            $table->foreignId('unidad_organizativa_id')->constrained('unidades_organizativas')->cascadeOnDelete();
            $table->string('event');
            $table->json('old_values')->nullable(); 
            $table->json('new_values')->nullable(); 
            $table->string('username')->nullable(); 
            $table->foreign('username')->references('username')->on('users')->nullOnDelete();
            $table->timestamp('created_at', 7)->useCurrent();
            
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