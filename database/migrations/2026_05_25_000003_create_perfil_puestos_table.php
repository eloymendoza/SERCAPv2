<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para la tabla perfil_puestos.
     */
    public function up(): void
    {
        Schema::create('perfil_puestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puesto_id')
                ->constrained('puestos')
                ->cascadeOnDelete();
            $table->foreignId('solicitud_id')
                ->nullable()
                ->constrained('solicitud_perfil_puestos')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('id_documento')->nullable()->comment('Referencia lógica a id_documento en BD externa');
            $table->dateTime('fecha_autorizacion', 7)->nullable();
            $table->string('estado')->default('inactivo')->comment('activo, inactivo (Histórico o pendiente)');
            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfil_puestos');
    }
};