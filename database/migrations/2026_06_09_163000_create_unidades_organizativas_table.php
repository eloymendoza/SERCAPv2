<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Construye la estructura de la tabla de unidades organizativas.
     */
    public function up(): void
    {
        Schema::create('unidades_organizativas', function (Blueprint $table) {
            $table->id();
            
            // Implementa Adjacency List para jerarquía dinámica (Dirección -> Gerencia -> Área)
            // Permite saltos de nivel asignando directamente al ancestro disponible.
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('unidades_organizativas');

            $table->string('nivel', 50);
            $table->string('nombre', 255);
            $table->string('abreviatura', 50)->nullable();
            $table->string('nombre_corto', 150)->nullable();
            $table->string('rfc', 20)->nullable();

            $table->foreignId('encargado_id')->nullable();

            $table->boolean('estado')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Revierte la creación de la tabla de unidades organizativas.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades_organizativas');
    }
};