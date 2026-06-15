<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para la tabla tabulador_salario.
     */
    public function up(): void
    {
        Schema::create('tabulador_salario', function (Blueprint $table) {
            $table->id();
            $table->string('nivel_categoria')->comment('Ej: Nivel 1, Nivel 2, Especialista A');
            $table->decimal('sueldo_minimo', 12, 2)->comment('Límite inferior del rango');
            $table->decimal('sueldo_maximo', 12, 2)->comment('Límite superior del rango');
            $table->string('estado')->default('activo')->comment('activo, inactivo');
            $table->timestamps(7);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabulador_salario');
    }
};
