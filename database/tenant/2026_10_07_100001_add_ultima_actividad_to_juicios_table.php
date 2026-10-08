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
        Schema::table('juicios', function (Blueprint $table) {
            $table->dateTime('ultima_actividad_at')->nullable()->after('fecha_inicio');
            $table->index('ultima_actividad_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('juicios', function (Blueprint $table) {
            $table->dropIndex(['ultima_actividad_at']);
            $table->dropColumn('ultima_actividad_at');
        });
    }
};
