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
            $table->unsignedBigInteger('encargado_id')->nullable()->comment('ID del encargado (actualmente apunta a aspirantes, a futuro a empleados)');
            $table->string('encargado_usuario', 255)->nullable();
            $table->unsignedBigInteger('reemplaza_a_id')->nullable()->comment('ID de la unidad a la que sustituye orgánicamente');
            $table->foreign('reemplaza_a_id')->references('id')->on('unidades_organizativas')->onDelete('no action');
            $table->string('estado', 50)->default('Activo')->comment('Activo, Inactivo, Borrador');
            $table->date('enabled_at')->nullable();
            $table->date('disabled_at')->nullable();
            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);
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