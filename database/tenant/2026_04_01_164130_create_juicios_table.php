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
        Schema::create('juicios', function (Blueprint $table) {
            $table->id();
            $table->string('cod_satje');
            $table->unsignedBigInteger('asunto_id');
            $table->unsignedBigInteger('estado_procesal_id');
            $table->foreign('asunto_id')->references('id')->on('asuntos')->onDelete('cascade');
            $table->foreign('estado_procesal_id')->references('id')->on('estados_procesales')->onDelete('cascade');
            $table->date('fecha_inicio');
            $table->enum('prioridad', ['Urgente', 'Alta', 'Media', 'Baja']);
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
        Schema::dropIfExists('juicios');
    }
};
