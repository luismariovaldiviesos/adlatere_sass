<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TareaHistorial extends Model
{
    use HasFactory;

    protected $table = 'tarea_historiales';

    protected $fillable = [
        'tarea_id',
        'user_id',
        'tipo',
        'descripcion',
        'datos_anteriores',
        'datos_nuevos',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
    ];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function registrar($tareaId, $tipo, $descripcion, $datosAnteriores = null, $datosNuevos = null)
    {
        return self::create([
            'tarea_id' => $tareaId,
            'user_id' => auth()->id(),
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
        ]);
    }

    public function getIconAttribute()
    {
        return match($this->tipo) {
            'creada' => 'fas fa-plus-circle text-green-500',
            'editada' => 'fas fa-edit text-blue-500',
            'estado_cambiado' => 'fas fa-exchange-alt text-yellow-500',
            'contenido_guardado' => 'fas fa-save text-purple-500',
            'adjunto_subido' => 'fas fa-paperclip text-indigo-500',
            'adjunto_eliminado' => 'fas fa-trash text-red-500',
            'convertida' => 'fas fa-gavel text-cyan-500',
            'eliminada' => 'fas fa-trash-alt text-red-700',
            'fecha_cambiada' => 'fas fa-calendar-alt text-amber-500',
            default => 'fas fa-info-circle text-gray-500',
        };
    }

    public function getLabelAttribute()
    {
        return match($this->tipo) {
            'creada' => 'Creada',
            'editada' => 'Editada',
            'estado_cambiado' => 'Estado cambiado',
            'contenido_guardado' => 'Contenido guardado',
            'adjunto_subido' => 'Adjunto subido',
            'adjunto_eliminado' => 'Adjunto eliminado',
            'convertida' => 'Convertida en actividad',
            'eliminada' => 'Eliminada',
            'fecha_cambiada' => 'Fecha de vencimiento cambiada',
            default => ucfirst($this->tipo),
        };
    }
}