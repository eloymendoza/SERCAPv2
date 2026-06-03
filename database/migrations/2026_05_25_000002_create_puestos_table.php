<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para la tabla puestos.
     */
    public function up(): void
    {
        Schema::create('puestos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_puesto');
            $table->integer('direccion_id');
            $table->foreignId('reporta_a_puesto_id')
                ->nullable()
                ->constrained('puestos')
                ->noActionOnDelete();
            $table->string('tipo')->default('Directo')->comment('Directo, Indirecto');
            $table->string('estado')->default('Borrador')->comment('Borrador, Activo, Inactivo');
            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('puestos');
    }
};