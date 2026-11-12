<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estados_procesales', function (Blueprint $table) {
            $table->dropUnique('estados_procesales_nombre_unique');
            $table->unique(['fase_id', 'nombre'], 'estados_procesales_fase_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('estados_procesales', function (Blueprint $table) {
            $table->dropUnique('estados_procesales_fase_nombre_unique');
            $table->unique('nombre', 'estados_procesales_nombre_unique');
        });
    }
};
