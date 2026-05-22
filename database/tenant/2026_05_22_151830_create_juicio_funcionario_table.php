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
        Schema::create('juicio_funcionario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('juicio_id')->constrained('juicios')->onDelete('cascade');
            $table->foreignId('funcionario_id')->constrained('funcionarios')->onDelete('cascade');
            $table->string('rol_en_juicio'); // Juez Ponente, Secretario, Fiscal de turno
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
        Schema::dropIfExists('juicio_funcionario');
    }
};
