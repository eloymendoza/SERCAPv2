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
        Schema::table('aspirantes', function (Blueprint $table) {
            // campos complementarios
            $table->string('tipo_aspirante')
                ->after('email');
            $table->integer('ubicacion_id')
                ->nullable()
                ->after('tipo_aspirante'); // referencia lógica a BD de ubicaciones
            $table->text('resumen')
                ->nullable()
                ->after('ubicacion_id');
            $table->string('estado_aspirante')
                ->after('resumen');

            // delimitando campos
            $table->string('nombres', 100)->change();
            $table->string('apellido_paterno', 100)->change();
            $table->string('apellido_materno', 100)->change();
            $table->string('telefono', 15)->nullable()->change();
            $table->string('email', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aspirantes', function (Blueprint $table) {
             // Revertir cambios de longitud (volver a los valores originales)
            $table->string('nombres', 255)->change();
            $table->string('apellido_paterno', 255)->change();
            $table->string('apellido_materno', 255)->change();
            $table->string('telefono', 255)->nullable()->change();
            $table->string('email', 255)->nullable()->change();
            
            // Eliminar los campos que agregaste
            $table->dropColumn([
                'tipo_aspirante',
                'ubicacion_id',
                'resumen',
                'estado_aspirante'
            ]);
        });
    }
};
