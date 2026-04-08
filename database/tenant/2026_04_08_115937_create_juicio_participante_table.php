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
        Schema::create('juicio_participante', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('juicio_id');
            $table->unsignedBigInteger('customer_id');
            $table->enum('rol',['actor', 'demandado']); // Ejemplo: demandante, demandado, representante
            $table->foreign('juicio_id')->references('id')->on('juicios')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
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
        Schema::dropIfExists('juicio_participante');
    }
};
