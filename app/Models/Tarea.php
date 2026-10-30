<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory, HasDocumentos;

    protected $fillable = [
        'titulo', 'descripcion', 'contenido', 'tipo', 'juicio_id', 'asignado_a',
        'creado_por', 'fecha_limite', 'estado', 'fecha_completada', 'actividad_id',
    ];

    protected $casts = [
        'fecha_limite' => 'date',
        'fecha_completada' => 'datetime',
    ];

    public function juicio() { return $this->belongsTo(Juicio::class); }
    public function asignado() { return $this->belongsTo(User::class, 'asignado_a'); }
    public function creador() { return $this->belongsTo(User::class, 'creado_por'); }
    public function actividad() { return $this->belongsTo(Actividad::class); }
    public function adjuntos() { return $this->hasMany(TareaAdjunto::class)->orderBy('id', 'desc'); }
    public function historiales() { return $this->hasMany(TareaHistorial::class)->orderBy('id', 'desc'); }

    public function promoverDocumentosAActividad(Actividad $actividad): void
    {
        if (!$this->juicio_id || (int) $actividad->juicio_id !== (int) $this->juicio_id) {
            return;
        }

        $this->loadMissing(['documentos', 'adjuntos']);
        $tipoAnexo = TipoDocumento::where('codigo', 'anexo')->value('id');

        foreach ($this->documentos as $documento) {
            $documento->vincularA($this, 'principal');
            $documento->vincularA($actividad, 'relacionado');
        }

        foreach ($this->adjuntos as $adjunto) {
            $documento = Documento::firstOrNew([
                'juicio_id' => $this->juicio_id,
                'ruta_archivo' => $adjunto->ruta_archivo,
            ]);

            if (!$documento->exists) {
                $documento->fill([
                    'tipo_documento_id' => $tipoAnexo,
                    'estado_procesal_id' => $actividad->juicio->estado_procesal_id ?? null,
                    'origen_tipo' => 'Tarea',
                    'origen_id' => $this->id,
                    'nombre' => $adjunto->nombre,
                    'descripcion' => 'Adjunto de la tarea #' . $this->id . ': ' . $this->titulo,
                    'fecha_documento' => $adjunto->created_at ? $adjunto->created_at->toDateString() : now()->toDateString(),
                    'tipo_archivo' => $adjunto->tipo_archivo,
                    'created_by' => $adjunto->user_id,
                ]);
                $documento->setAttribute('tamaño_archivo', $adjunto->tamaño_archivo ?? 0);
                $documento->save();
            }

            $documento->vincularA($this, 'relacionado');
            $documento->vincularA($actividad, 'principal');
        }
    }

    public function getEstaVencidaAttribute()
    {
        return $this->fecha_limite
            && $this->fecha_limite->isBefore(today())
            && !in_array($this->estado, ['completada', 'convertida', 'cancelada']);
    }

    public function scopeMias($q, $userId = null)
    {
        return $q->where('asignado_a', $userId ?: auth()->id());
    }

    public function scopeAbiertas($q)
    {
        return $q->whereIn('estado', ['pendiente', 'en_proceso']);
    }
}
