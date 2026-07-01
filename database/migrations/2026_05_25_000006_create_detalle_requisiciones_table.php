<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para la tabla detalle_requisiciones.
     */
    public function up(): void
    {
        Schema::create('detalle_requisiciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisicion_id')
                ->constrained('requisiciones')
                ->cascadeOnDelete();
            $table->foreignId('puesto_id')
                ->nullable()
                ->constrained('puestos');
            $table->integer('cantidad_solicitada');
            $table->foreignId('disciplina_id')
                ->nullable()
                ->constrained('disciplinas');
            $table->foreignId('tabulador_id')
                ->constrained('tabulador_salario')
                ->comment('Compensación económica global');
            $table->decimal('sueldo_asignado', 12, 2)
                ->comment('Sueldo exacto definido por DIR/GA/JP/LI dentro del rango del tabulador');
            $table->string('turno_horas');
            $table->string('tipo_contrato')->comment('obra_determinada, tiempo_determinado, tiempo_indeterminado');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_termino')->nullable();
            $table->date('fecha_limite_requerimiento');
            $table->string('empleados_propuestos')->nullable()->comment('IDs de candidatos opcionales separados por coma');
            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_requisiciones');
    }
};