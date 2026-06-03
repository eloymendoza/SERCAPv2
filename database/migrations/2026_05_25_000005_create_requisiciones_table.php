<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para la tabla requisiciones.
     */
    public function up(): void
    {
        Schema::create('requisiciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')
                ->unique()
                ->constrained('solicitud_requisiciones')
                ->cascadeOnDelete();
            $table->string('folio')->nullable()->comment('Ej: RPIACDS99926-1');
            $table->integer('tipo')->nullable()->comment('0 corporativo, 1 CLV');
            $table->string('observaciones')->nullable();
            $table->string('estado')->default('Borrador')->comment('Borrador, Pendiente_Perfil, Abierta, En_Proceso, Cierre_Parcial, Validacion_Admin, Cubierta, Cancelada');
            $table->timestamps(7);
            $table->softDeletes('deleted_at', 7);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisiciones');
    }
};