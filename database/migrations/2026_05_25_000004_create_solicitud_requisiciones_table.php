<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para la tabla solicitud_requisiciones.
     */
    public function up(): void
    {
        Schema::create('solicitud_requisiciones', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique()->comment('Ej: SRPIACDS-001');
            $table->unsignedBigInteger('proyecto_id')->nullable()
                ->comment('Referencia lógica a id_proyecto de BD Costosv2');
            $table->unsignedBigInteger('id_instancia_workflow')->nullable()
                ->comment('Referencia lógica a id_instancia_workflow de BD Workflows');
            $table->unsignedBigInteger('solicitante_id')->nullable()->comment('id_empleado (DIR, GA, JP o LI)');
            $table->string('direccion');
            $table->string('gerencia');
            $table->string('coordinacion')->nullable();
            $table->string('observaciones')->nullable();
            $table->string('estado')->comment('Borrador, En Proceso, Rechazado, Cancelado, Terminado');
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_requisiciones');
    }
};