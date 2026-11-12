<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('materias', 'unidad_id')) {
            Schema::table('materias', function (Blueprint $table) {
                $table->dropForeign(['unidad_id']);
            });

            Schema::table('materias', function (Blueprint $table) {
                $table->dropColumn('unidad_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('materias', 'unidad_id')) {
            Schema::table('materias', function (Blueprint $table) {
                $table->unsignedBigInteger('unidad_id')->nullable()->after('nombre');
            });

            Schema::table('materias', function (Blueprint $table) {
                $table->foreign('unidad_id')->references('id')->on('unidads')->nullOnDelete();
            });
        }
    }
};
