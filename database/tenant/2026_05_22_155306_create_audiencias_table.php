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
        Schema::create('audiencias', function (Blueprint $table) {
             $table->id();
            $table->foreignId('juicio_id')->constrained('juicios')->onDelete('cascade');
            $table->dateTime('fecha_hora');
            $table->string('tipo_audiencia'); // Formulación de Cargos, Preparatoria, etc.
            $table->string('sala_enlace')->nullable(); // "# Sala 4" o enlace de Zoom
            $table->string('estado')->default('Programada'); // Programada, Realizada, Suspendida
            $table->text('acta_resumen')->nullable(); // Resumen o acta de lo ocurrido en la audiencia
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audiencias');
    }
};
