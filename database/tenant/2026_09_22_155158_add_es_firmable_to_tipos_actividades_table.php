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
        Schema::table('tipos_actividades', function (Blueprint $table) {
            $table->boolean('es_firmable')->default(false)->after('nombre');
             $table->boolean('requiere_firma_abogado')->default(true)->after('es_firmable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tipos_actividades', function (Blueprint $table) {
            //
        });
    }
};
