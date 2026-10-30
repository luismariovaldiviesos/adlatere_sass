<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipos_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 120);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $ahora = now();
        DB::table('tipos_documentos')->insertOrIgnore([
            ['codigo' => 'demanda', 'nombre' => 'Demanda', 'orden' => 10, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'contestacion', 'nombre' => 'Contestación', 'orden' => 20, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'poder', 'nombre' => 'Poder', 'orden' => 30, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'cedula', 'nombre' => 'Cédula / Identificación', 'orden' => 40, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'escrito', 'nombre' => 'Escrito', 'orden' => 50, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'prueba', 'nombre' => 'Prueba', 'orden' => 60, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'acta', 'nombre' => 'Acta', 'orden' => 70, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'resolucion', 'nombre' => 'Resolución', 'orden' => 80, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'oficio', 'nombre' => 'Oficio', 'orden' => 90, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'notificacion', 'nombre' => 'Notificación', 'orden' => 100, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'comprobante', 'nombre' => 'Comprobante', 'orden' => 110, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'anexo', 'nombre' => 'Anexo', 'orden' => 120, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'general', 'nombre' => 'General / Otros', 'orden' => 130, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);

        Schema::table('documentos', function (Blueprint $table) {
            if (!Schema::hasColumn('documentos', 'tipo_documento_id')) {
                $table->foreignId('tipo_documento_id')->nullable()->after('juicio_id')
                    ->constrained('tipos_documentos')->nullOnDelete();
            }
            if (!Schema::hasColumn('documentos', 'estado_procesal_id')) {
                $table->foreignId('estado_procesal_id')->nullable()->after('tipo_documento_id')
                    ->constrained('estados_procesales')->nullOnDelete();
            }
            if (!Schema::hasColumn('documentos', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('documentos', 'fecha_documento')) {
                $table->date('fecha_documento')->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('documentos', 'hash')) {
                $table->string('hash', 64)->nullable()->index()->after('tipo_archivo');
            }
            if (!Schema::hasColumn('documentos', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('hash')
                    ->constrained('users')->nullOnDelete();
            }
        });

        Schema::create('documento_vinculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documentos')->cascadeOnDelete();
            $table->morphs('vinculable');
            $table->string('tipo_vinculo', 20)->default('principal');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['documento_id', 'vinculable_type', 'vinculable_id'], 'documento_vinculo_unico');
        });

        $tipos = DB::table('tipos_documentos')->pluck('id', 'codigo');
        $estadosPorJuicio = DB::table('juicios')->pluck('estado_procesal_id', 'id');
        $tiposPorOrigen = [
            'Actividad' => 'escrito',
            'Audiencia' => 'acta',
            'Consulta' => 'comprobante',
            'Carátula / Demanda' => 'demanda',
            'Sujetos Procesales' => 'poder',
            'General' => 'general',
        ];

        DB::table('documentos')->orderBy('id')->chunkById(100, function ($documentos) use ($tipos, $estadosPorJuicio, $tiposPorOrigen) {
            foreach ($documentos as $documento) {
                $tipoCodigo = $tiposPorOrigen[$documento->origen_tipo] ?? 'general';
                $origenTipo = trim((string) $documento->origen_tipo);
                $origenId = $documento->origen_id;
                $morphAlias = null;

                if ($origenTipo === 'Actividad') {
                    $actividad = null;
                    if ($origenId) {
                        $actividad = DB::table('actividades')
                            ->where('id', $origenId)
                            ->where('juicio_id', $documento->juicio_id)
                            ->first();
                    }
                    if (!$actividad) {
                        $actividad = DB::table('actividades')
                            ->where('juicio_id', $documento->juicio_id)
                            ->whereRaw('TRIM(descripcion) = ?', [trim((string) $documento->nombre)])
                            ->orderByDesc('id')
                            ->first();
                    }
                    if ($actividad) {
                        $morphAlias = 'actividad';
                        $origenId = $actividad->id;
                    }
                } elseif ($origenTipo === 'Audiencia' && $origenId) {
                    $existe = DB::table('audiencias')->where('id', $origenId)->where('juicio_id', $documento->juicio_id)->exists();
                    if ($existe) $morphAlias = 'audiencia';
                } elseif ($origenTipo === 'Consulta' && $origenId) {
                    $existe = DB::table('consultas')->where('id', $origenId)->where('juicio_id', $documento->juicio_id)->exists();
                    if ($existe) $morphAlias = 'consulta';
                }

                DB::table('documentos')->where('id', $documento->id)->update([
                    'tipo_documento_id' => $tipos[$tipoCodigo] ?? null,
                    'estado_procesal_id' => $estadosPorJuicio[$documento->juicio_id] ?? null,
                    'updated_at' => now(),
                ]);

                if ($morphAlias) {
                    DB::table('documento_vinculos')->insertOrIgnore([
                        'documento_id' => $documento->id,
                        'vinculable_type' => $morphAlias,
                        'vinculable_id' => $origenId,
                        'tipo_vinculo' => 'principal',
                        'created_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down()
    {
        Schema::table('documentos', function (Blueprint $table) {
            if (Schema::hasColumn('documentos', 'tipo_documento_id')) {
                $table->dropConstrainedForeignId('tipo_documento_id');
            }
            if (Schema::hasColumn('documentos', 'estado_procesal_id')) {
                $table->dropConstrainedForeignId('estado_procesal_id');
            }
            if (Schema::hasColumn('documentos', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('documentos', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
            if (Schema::hasColumn('documentos', 'fecha_documento')) {
                $table->dropColumn('fecha_documento');
            }
            if (Schema::hasColumn('documentos', 'hash')) {
                $table->dropColumn('hash');
            }
        });

        Schema::dropIfExists('documento_vinculos');
        Schema::dropIfExists('tipos_documentos');
    }
};
