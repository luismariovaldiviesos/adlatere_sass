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
            $table->unsignedBigInteger('created_by')->nullable()->after('id');
            $table->index('created_by');
        });

        if (Schema::hasTable('juicio_historial_estados')) {
            $creadores = DB::table('juicio_historial_estados')
                ->where('tipo_movimiento', 'juicio_creado')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->get(['juicio_id', 'user_id'])
                ->unique('juicio_id')
                ->pluck('user_id', 'juicio_id');

            foreach ($creadores as $juicioId => $userId) {
                DB::table('juicios')
                    ->where('id', $juicioId)
                    ->whereNull('created_by')
                    ->update(['created_by' => $userId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('juicios', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
