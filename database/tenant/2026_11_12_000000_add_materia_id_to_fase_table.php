<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fase', function (Blueprint $table) {
            $table->foreignId('materia_id')
                ->nullable()
                ->after('nombre')
                ->constrained('materias')
                ->nullOnDelete();
        });

        $pares = DB::table('juicios')
            ->join('estados_procesales', 'estados_procesales.id', '=', 'juicios.estado_procesal_id')
            ->join('fase', 'fase.id', '=', 'estados_procesales.fase_id')
            ->join('asuntos', 'asuntos.id', '=', 'juicios.asunto_id')
            ->join('procedimientos', 'procedimientos.id', '=', 'asuntos.procedimiento_id')
            ->select('fase.id as fase_id', 'procedimientos.materia_id')
            ->distinct()
            ->get()
            ->groupBy('fase_id');

        foreach ($pares as $faseId => $filas) {
            $materias = $filas->pluck('materia_id')->unique()->values();

            if ($materias->count() === 1) {
                DB::table('fase')
                    ->where('id', $faseId)
                    ->whereNull('materia_id')
                    ->update(['materia_id' => $materias->first()]);
            } else {
                Log::warning("Fase {$faseId} está relacionada con varias materias; requiere revisión manual.");
            }
        }

        // Asignación segura por nombre para fases aún sin materia:
        // solo cuando el nombre de la fase contiene exactamente una materia.
        $materias = DB::table('materias')->get(['id', 'nombre']);

        foreach (DB::table('fase')->whereNull('materia_id')->get(['id', 'nombre']) as $fase) {
            $coincidencias = $materias->filter(function ($materia) use ($fase) {
                return Str::contains(
                    Str::lower($fase->nombre),
                    Str::lower($materia->nombre)
                );
            });

            if ($coincidencias->count() === 1) {
                DB::table('fase')
                    ->where('id', $fase->id)
                    ->update(['materia_id' => $coincidencias->first()->id]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('fase', 'materia_id')) {
            Schema::table('fase', function (Blueprint $table) {
                $table->dropForeign(['materia_id']);
            });

            Schema::table('fase', function (Blueprint $table) {
                $table->dropColumn('materia_id');
            });
        }
    }
};
