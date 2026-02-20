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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('rimpe_type')->default('Ninguno')->after('secuencial_factura'); // Ninguno, Negocio Popular, Emprendedor
            $table->string('agente_retencion')->nullable()->after('rimpe_type'); // Resolution Number
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('rimpe_type');
            $table->dropColumn('agente_retencion');
        });
    }
};
