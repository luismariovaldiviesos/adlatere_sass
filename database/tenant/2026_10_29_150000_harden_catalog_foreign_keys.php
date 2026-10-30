<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $restrict = [
        ['juicios', 'estado_procesal_id', 'estados_procesales'],
        ['juicios', 'asunto_id', 'asuntos'],
        ['juicios', 'unidad_id', 'unidads'],
        ['juicio_historial_estados', 'estado_procesal_id', 'estados_procesales'],
        ['juicio_historial_estados', 'user_id', 'users'],
        ['actividades', 'tipo_actividad_id', 'tipos_actividades'],
        ['plantillas_tipos_actividad', 'tipo_actividad_id', 'tipos_actividades'],
        ['estados_procesales', 'fase_id', 'fase'],
        ['asuntos', 'procedimiento_id', 'procedimientos'],
        ['procedimientos', 'materia_id', 'materias'],
        ['especialidades', 'materia_id', 'materias'],
        ['cantones', 'provincia_id', 'provincias'],
        ['unidads', 'canton_id', 'cantones'],
        ['materias', 'unidad_id', 'unidads'],
        ['consultas', 'customer_id', 'customers'],
        ['juicio_participante', 'customer_id', 'customers'],
        ['tareas', 'asignado_a', 'users'],
        ['tarea_historiales', 'user_id', 'users'],
        ['pagos_juicios', 'user_id', 'users'],
        ['juicio_user', 'user_id', 'users'],
    ];

    public function up()
    {
        foreach ($this->restrict as [$tabla, $columna, $referencia]) {
            if (!Schema::hasTable($tabla) || !Schema::hasColumn($tabla, $columna)) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) use ($columna, $referencia) {
                $table->dropForeign([$columna]);
                $table->foreign($columna)->references('id')->on($referencia)->onDelete('restrict');
            });
        }
    }

    public function down()
    {
        $originales = [
            ['juicios', 'estado_procesal_id', 'estados_procesales', 'cascade'],
            ['juicios', 'asunto_id', 'asuntos', 'cascade'],
            ['juicios', 'unidad_id', 'unidads', 'set null'],
            ['juicio_historial_estados', 'estado_procesal_id', 'estados_procesales', 'cascade'],
            ['juicio_historial_estados', 'user_id', 'users', 'cascade'],
            ['actividades', 'tipo_actividad_id', 'tipos_actividades', 'cascade'],
            ['plantillas_tipos_actividad', 'tipo_actividad_id', 'tipos_actividades', 'cascade'],
            ['estados_procesales', 'fase_id', 'fase', 'cascade'],
            ['asuntos', 'procedimiento_id', 'procedimientos', 'cascade'],
            ['procedimientos', 'materia_id', 'materias', 'cascade'],
            ['especialidades', 'materia_id', 'materias', 'cascade'],
            ['cantones', 'provincia_id', 'provincias', 'cascade'],
            ['unidads', 'canton_id', 'cantones', 'cascade'],
            ['materias', 'unidad_id', 'unidads', 'set null'],
            ['consultas', 'customer_id', 'customers', 'cascade'],
            ['juicio_participante', 'customer_id', 'customers', 'cascade'],
            ['tareas', 'asignado_a', 'users', 'cascade'],
            ['tarea_historiales', 'user_id', 'users', 'cascade'],
            ['pagos_juicios', 'user_id', 'users', 'cascade'],
            ['juicio_user', 'user_id', 'users', 'cascade'],
        ];

        foreach ($originales as [$tabla, $columna, $referencia, $regla]) {
            if (!Schema::hasTable($tabla) || !Schema::hasColumn($tabla, $columna)) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) use ($columna, $referencia, $regla) {
                $table->dropForeign([$columna]);
                $foreign = $table->foreign($columna)->references('id')->on($referencia);
                $regla === 'cascade' ? $foreign->onDelete('cascade') : $foreign->onDelete('set null');
            });
        }
    }
};
