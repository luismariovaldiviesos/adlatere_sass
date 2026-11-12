<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia_unidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidads')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['materia_id', 'unidad_id']);
            $table->index(['unidad_id', 'materia_id']);
        });

        $now = now();
        $pairs = [];

        $desdeJuicios = DB::table('juicios')
            ->join('asuntos', 'asuntos.id', '=', 'juicios.asunto_id')
            ->join('procedimientos', 'procedimientos.id', '=', 'asuntos.procedimiento_id')
            ->whereNotNull('juicios.unidad_id')
            ->select('procedimientos.materia_id', 'juicios.unidad_id')
            ->distinct()
            ->get();

        foreach ($desdeJuicios as $par) {
            $pairs[] = [
                'materia_id' => $par->materia_id,
                'unidad_id' => $par->unidad_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (Schema::hasColumn('materias', 'unidad_id')) {
            foreach (DB::table('materias')->whereNotNull('unidad_id')->get(['id', 'unidad_id']) as $materia) {
                $pairs[] = [
                    'materia_id' => $materia->id,
                    'unidad_id' => $materia->unidad_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($pairs, 500) as $chunk) {
            DB::table('materia_unidad')->insertOrIgnore($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('materia_unidad');
    }
};
