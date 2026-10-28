<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('juicios', function (Blueprint $table) {
            $table->timestamp('base_confirmada_at')->nullable()->after('sujetos_fijados_at');
            $table->unsignedBigInteger('base_confirmada_por')->nullable()->after('base_confirmada_at');
        });

        DB::table('juicios')
            ->whereNotNull('sujetos_fijados_at')
            ->whereNull('base_confirmada_at')
            ->update(['base_confirmada_at' => DB::raw('sujetos_fijados_at')]);
    }

    public function down(): void
    {
        Schema::table('juicios', function (Blueprint $table) {
            $table->dropColumn(['base_confirmada_at', 'base_confirmada_por']);
        });
    }
};
