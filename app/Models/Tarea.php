<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

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
