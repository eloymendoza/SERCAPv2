<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unidad_organizativa_jerarquia_historica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('padre_id')->nullable()->constrained('unidades_organizativas');
            $table->foreignId('hijo_id')->constrained('unidades_organizativas');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->string('usuario', 255)->nullable()->comment('Usuario que realizó la acción');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidad_organizativa_jerarquia_historica');
    }
};