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
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('juicio_id')->constrained('juicios')->onDelete('cascade');
            $table->foreignId('tipo_actividad_id')->constrained('tipos_actividades')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Quién registra
            $table->enum('origen', ['Interno', 'Externo'])->default('Interno');
            $table->dateTime('fecha_actividad');
            $table->mediumText('descripcion')->nullable(); // Resumen breve
            $table->longText('contenido')->nullable(); // El texto de la plantilla o actuación
            $table->string('archivo')->nullable(); // Para adjuntos del SATJE
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
        Schema::dropIfExists('actividades');
    }
};
