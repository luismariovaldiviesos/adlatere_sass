<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->string('firma_origen', 20)
                ->nullable()
                ->after('estado_firma');
            $table->string('firma_titular', 255)
                ->nullable()
                ->after('firma_origen');
        });
    }

    public function down(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropColumn(['firma_origen', 'firma_titular']);
        });
    }
};
