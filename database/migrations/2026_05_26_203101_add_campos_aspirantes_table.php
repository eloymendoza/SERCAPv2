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
            $table->enum('tipo_aspirante', ['nuevo_aspirante', 'personal_activo', 'personal_anterior'])
                ->default('nuevo_aspirante')
                ->after('email');
            $table->integer('Id_personal')
                ->nullable()
                ->after('tipo_aspirante'); // referencia lógica a la info de SERCAP legacy
            $table->integer('ubicacion_id')
                ->nullable()
                ->after('Id_personal'); // referencia lógica a BD de ubicaciones
            $table->text('resumen')
                ->nullable()
                ->after('ubicacion_id');
            $table->enum('estado_aspirante', ['nuevo', 'en_revision', 'reclutado', 'rechazado', 'contratado'])
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
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlsrv') {
            // SQL Server requiere eliminar los CHECK constraints generados por los enum() antes de poder borrar las columnas
            \Illuminate\Support\Facades\DB::statement("
                DECLARE @sql NVARCHAR(MAX) = N'';
                SELECT @sql += N'ALTER TABLE aspirantes DROP CONSTRAINT ' + name + ';'
                FROM sys.check_constraints
                WHERE parent_object_id = OBJECT_ID('aspirantes');
                EXEC sp_executesql @sql;
            ");
        }

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
                'Id_personal', 
                'ubicacion_id',
                'resumen',
                'estado_aspirante'
            ]);
        });
    }
};
