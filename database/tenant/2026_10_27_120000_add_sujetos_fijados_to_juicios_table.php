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
            $table->timestamp('sujetos_fijados_at')->nullable()->after('ultima_actividad_at');
        });

        $juicioIds = DB::table('actividades')
            ->whereNotNull('juicio_id')
            ->where(function ($q) {
                $q->whereNull('descripcion')
                    ->orWhere('descripcion', 'not like', 'Nota inicial de la consulta #%');
            })
            ->select('juicio_id')
            ->union(DB::table('audiencias')->whereNotNull('juicio_id')->select('juicio_id'))
            ->union(DB::table('documentos')
                ->whereNotNull('juicio_id')
                ->where(function ($q) {
                    $q->whereNull('origen_tipo')->orWhere('origen_tipo', '!=', 'Consulta');
                })
                ->select('juicio_id'))
            ->pluck('juicio_id')
            ->unique()
            ->all();

        if (!empty($juicioIds)) {
            DB::table('juicios')
                ->whereIn('id', $juicioIds)
                ->whereNull('sujetos_fijados_at')
                ->update(['sujetos_fijados_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('juicios', function (Blueprint $table) {
            $table->dropColumn('sujetos_fijados_at');
        });
    }
};
