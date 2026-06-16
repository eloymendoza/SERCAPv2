<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para la tabla solicitud_perfil_puestos.
     */
    public function up(): void
    {
        Schema::create('solicitud_perfil_puestos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitante_id')->comment('id_empleado (DIR, GA, JP o LI)');
            $table->unsignedBigInteger('id_instancia_workflow')->nullable()
                ->comment('Referencia lógica a BD externa para gestión de firmas');
            $table->unsignedBigInteger('direccion_id');
            $table->unsignedBigInteger('gerencia_id')->nullable();
            $table->unsignedBigInteger('coordinacion_id')->nullable();
            $table->string('observaciones')->nullable();
            $table->string('estado')->default('borrador')->comment('borrador, en_proceso, rechazado, cancelado, terminado');
            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_perfil_puestos');
    }
};
