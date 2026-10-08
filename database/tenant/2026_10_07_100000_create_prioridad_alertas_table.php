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
        Schema::create('prioridad_alertas', function (Blueprint $table) {
            $table->id();
            $table->string('prioridad', 20)->unique();
            $table->unsignedSmallInteger('dias_verde')->default(5);
            $table->unsignedSmallInteger('dias_amarillo')->default(15);
            $table->unsignedSmallInteger('dias_rojo')->default(25);
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
        Schema::dropIfExists('prioridad_alertas');
    }
};
