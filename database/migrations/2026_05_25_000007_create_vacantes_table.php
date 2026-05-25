<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para la tabla vacantes.
     */
    public function up(): void
    {
        Schema::create('vacantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detalle_requisicion_id')
                ->constrained('detalle_requisiciones')
                ->cascadeOnDelete();
            $table->foreignId('aspirante_id')
                ->nullable()
                ->unique()
                ->constrained('aspirantes')
                ->nullOnDelete();
            $table->dateTime('fecha_limite_anexo_a', 7)->nullable()->comment('SLA 3 días');
            $table->decimal('sueldo_anexo_d', 12, 2)->nullable();
            $table->string('observaciones_rechazo')->nullable();
            $table->date('fecha_alta_imss')->nullable();
            $table->string('estado')->comment('Pendiente_Perfil, Busqueda_Activa, Seleccionada, En_Auditoria, Contratada, Cancelada');
            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacantes');
    }
};