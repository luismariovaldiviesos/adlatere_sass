<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Marca qué participante(s) son clientes del despacho, independiente del rol.
     * Un mismo customer puede ser actor en un juicio y demandado en otro.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('juicio_participante', function (Blueprint $table) {
            $table->boolean('es_cliente')->default(false)->after('rol');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('juicio_participante', function (Blueprint $table) {
            $table->dropColumn('es_cliente');
        });
    }
};
