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
        Schema::table('prioridad_alertas', function (Blueprint $table) {
            $table->string('mensaje', 255)->nullable()->after('dias_rojo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prioridad_alertas', function (Blueprint $table) {
            $table->dropColumn('mensaje');
        });
    }
};
