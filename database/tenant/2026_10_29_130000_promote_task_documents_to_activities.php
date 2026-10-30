<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $tipoAnexo = DB::table('tipos_documentos')->where('codigo', 'anexo')->value('id');

        $tareas = DB::table('tareas')->whereNotNull('actividad_id')->get();

        foreach ($tareas as $tarea) {
            $juicio = DB::table('juicios')->find($tarea->juicio_id);
            if (!$juicio) {
                continue;
            }

            $documentosDeTarea = DB::table('documento_vinculos')
                ->where('vinculable_type', 'tarea')
                ->where('vinculable_id', $tarea->id)
                ->pluck('documento_id');

            foreach ($documentosDeTarea as $documentoId) {
                DB::table('documento_vinculos')->insertOrIgnore([
                    'documento_id' => $documentoId,
                    'vinculable_type' => 'actividad',
                    'vinculable_id' => $tarea->actividad_id,
                    'tipo_vinculo' => 'relacionado',
                    'created_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $adjuntos = DB::table('tarea_adjuntos')->where('tarea_id', $tarea->id)->get();

            foreach ($adjuntos as $adjunto) {
                $documentoId = DB::table('documentos')
                    ->where('juicio_id', $tarea->juicio_id)
                    ->where('ruta_archivo', $adjunto->ruta_archivo)
                    ->value('id');

                if (!$documentoId) {
                    $documentoId = DB::table('documentos')->insertGetId([
                        'juicio_id' => $tarea->juicio_id,
                        'tipo_documento_id' => $tipoAnexo,
                        'estado_procesal_id' => $juicio->estado_procesal_id,
                        'origen_tipo' => 'Tarea',
                        'origen_id' => $tarea->id,
                        'nombre' => $adjunto->nombre,
                        'descripcion' => 'Adjunto de la tarea #' . $tarea->id . ': ' . $tarea->titulo,
                        'fecha_documento' => $adjunto->created_at ? substr($adjunto->created_at, 0, 10) : now()->toDateString(),
                        'ruta_archivo' => $adjunto->ruta_archivo,
                        'tipo_archivo' => $adjunto->tipo_archivo,
                        'tamaño_archivo' => $adjunto->tamaño_archivo,
                        'created_by' => $adjunto->user_id,
                        'created_at' => $adjunto->created_at ?: now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('documento_vinculos')->insertOrIgnore([
                    'documento_id' => $documentoId,
                    'vinculable_type' => 'tarea',
                    'vinculable_id' => $tarea->id,
                    'tipo_vinculo' => 'relacionado',
                    'created_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('documento_vinculos')->insertOrIgnore([
                    'documento_id' => $documentoId,
                    'vinculable_type' => 'actividad',
                    'vinculable_id' => $tarea->actividad_id,
                    'tipo_vinculo' => 'principal',
                    'created_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        // Migración de datos: no se revierte para no perder vínculos documentales.
    }
};
