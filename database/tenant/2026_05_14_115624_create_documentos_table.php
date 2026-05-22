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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('juicio_id')->constrained('juicios')->onDelete('cascade');
            $table->string('origen_tipo')->nullable(); // Ej: 'Actividad', 'Audiencia', 'Juicio'
            $table->unsignedBigInteger('origen_id')->nullable(); // ID del registro de origen
            $table->string('nombre');
            $table->string('ruta_archivo');
            $table->string('tipo_archivo', 10)->nullable();
            $table->integer('tamaño_archivo')->nullable(); // Tamaño en bytes
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
        Schema::dropIfExists('documentos');
    }
};
