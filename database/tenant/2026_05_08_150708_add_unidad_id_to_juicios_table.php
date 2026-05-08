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
            $table->unsignedBigInteger('unidad_id')->nullable()->after('id');
            $table->foreign('unidad_id')->references('id')->on('unidads')->onDelete('set null');
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
            $table->dropForeign(['unidad_id']);
            $table->dropColumn('unidad_id');
        });
    }
};
