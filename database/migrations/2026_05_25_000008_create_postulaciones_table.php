<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para la tabla postulaciones.
     */
    public function up(): void
    {
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aspirante_id')
                ->constrained('aspirantes')
                ->cascadeOnDelete();
            $table->foreignId('detalle_requisicion_id')
                ->constrained('detalle_requisiciones')
                ->cascadeOnDelete();
            $table->string('tipo_movimiento')->comment('Calculado: Nuevo Ingreso, Reingreso, Mov. Interno');
            $table->dateTime('fecha_inicio_sla_entrevista')->nullable()->comment('SLA 3 o 5 días hábiles');
            $table->string('resultado_entrevista')->nullable();
            $table->string('resultado_examen_tecnico')->nullable();
            $table->string('resultado_medico')->nullable();
            $table->string('resultado_psicometrico')->nullable();
            $table->string('notas_autorizacion_excepcional')->nullable();
            $table->string('estado')->comment('Preseleccionado, En_Entrevista, En_Examenes, Autorizacion_Excepcional, Rechazado, Seleccionado');
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};