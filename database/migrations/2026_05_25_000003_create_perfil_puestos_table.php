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
            $table->unsignedBigInteger('id_documento')->nullable()->comment('Referencia lógica a id_documento en BD externa');
            $table->string('nivel_organizacional')->comment('Operativo, Mando Medio, Alta Dirección');
            $table->string('identificacion')->comment('Ej: COR-123/26');
            $table->integer('revision')->default(0);
            $table->string('mision_puesto');
            $table->text('funciones_responsabilidades');
            $table->text('relaciones_internas');
            $table->text('relaciones_externas');
            $table->text('autoridades_puesto');
            $table->json('manejo_recursos');
            $table->string('escolaridad_requerida');
            $table->text('otros_conocimientos');
            $table->string('experiencia_laboral');
            $table->string('herramientas_software');
            $table->string('idiomas');
            $table->json('competencias_organizacionales');
            $table->json('competencias_funcionales');
            $table->boolean('requiere_examen_vista')->default(false);
            $table->boolean('requiere_examen_medico')->default(false);
            $table->boolean('requiere_examen_psicometrico')->default(false);
            $table->boolean('requiere_evaluacion_tecnica')->default(false);
            $table->string('otros_examenes')->nullable();
            $table->dateTime('fecha_autorizacion', 7)->nullable();
            $table->string('estado')->default('Borrador')->comment('Borrador, En Proceso, Rechazado, Terminado, Cancelado');
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