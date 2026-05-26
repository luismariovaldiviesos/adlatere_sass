<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('juicio_historial_estados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('juicio_id')->constrained('juicios')->onDelete('cascade');
             $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('estado_procesal_id')->nullable()->constrained('estados_procesales')->onDelete('cascade');
            $table->string('tipo_movimiento');
             // Valores sugeridos para tipo_movimiento:
            // 'actividad_creada', 'actividad_editada', 'actividad_eliminada'
            // 'audiencia_programada', 'audiencia_realizada', 'audiencia_suspendida'
            // 'pago_registrado', 'pago_eliminado'
            // 'funcionario_asignado', 'funcionario_removido'
            // 'litigante_agregado', 'litigante_removido'
            // 'cambio_estado_procesal'
            // 'documento_subido'
            // Referencia polimórfica opcional: permite saber exactamente qué registro generó este evento
            $table->string('referencia_tipo')->nullable(); // Ej: 'Actividad', 'Audiencia', 'PagoJuicio'
            $table->unsignedBigInteger('referencia_id')->nullable(); // El ID del registro que lo generó
            $table->text('descripcion')->nullable(); // Descripción breve legible del evento
              // Solo created_at: este registro nunca se edita, es un log inmutable
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('juicio_historial_estados');
    }
};
