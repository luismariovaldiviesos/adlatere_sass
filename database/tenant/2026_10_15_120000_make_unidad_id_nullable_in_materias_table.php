<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropForeign(['unidad_id']);
        });
        DB::statement('ALTER TABLE `materias` MODIFY `unidad_id` BIGINT UNSIGNED NULL');
        Schema::table('materias', function (Blueprint $table) {
            $table->foreign('unidad_id')->references('id')->on('unidads')->onDelete('set null');
        });
    }

    public function down()
    {
        DB::statement('UPDATE `materias` SET `unidad_id` = NULL WHERE `unidad_id` IS NULL');
        Schema::table('materias', function (Blueprint $table) {
            $table->dropForeign(['unidad_id']);
        });
        DB::statement('ALTER TABLE `materias` MODIFY `unidad_id` BIGINT UNSIGNED NOT NULL');
        Schema::table('materias', function (Blueprint $table) {
            $table->foreign('unidad_id')->references('id')->on('unidads')->onDelete('cascade');
        });
    }
};
