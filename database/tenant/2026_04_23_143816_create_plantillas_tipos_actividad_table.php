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
        Schema::create('plantillas_tipos_actividad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('tipo_actividad_id')->constrained('tipo_actividad')->onDelete('cascade');
            $table->longText('contenido');
            $table->boolean('activo')->default(true);
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
        Schema::dropIfExists('plantillas_tipos_actividad');
    }
};
