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
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['externa', 'juicio'])->default('externa');
            $table->foreignId('juicio_id')->nullable()->constrained('juicios')->nullOnDelete();
            $table->foreignId('asignado_a')->constrained('users')->onDelete('cascade');
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_limite')->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'completada', 'convertida', 'cancelada'])->default('pendiente');
            $table->dateTime('fecha_completada')->nullable();
            $table->foreignId('actividad_id')->nullable()->constrained('actividades')->nullOnDelete();
            $table->timestamps();
            $table->index(['asignado_a', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tareas');
    }
};
