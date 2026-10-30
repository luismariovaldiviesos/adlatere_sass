<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentoVinculo extends Model
{
    use HasFactory;

    protected $table = 'documento_vinculos';

    protected $fillable = [
        'documento_id',
        'vinculable_type',
        'vinculable_id',
        'tipo_vinculo',
        'created_by',
    ];

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    public function vinculable()
    {
        return $this->morphTo();
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getEtiquetaAttribute(): string
    {
        $modelo = $this->vinculable;

        if (!$modelo) {
            return 'Registro eliminado';
        }

        if ($modelo instanceof Actividad) {
            $detalle = $modelo->descripcion ?: ($modelo->tipoActividad->nombre ?? 'Actividad');
            return 'Actividad #' . $modelo->id . ' · ' . Str::limit($detalle, 55);
        }

        if ($modelo instanceof Audiencia) {
            return 'Audiencia #' . $modelo->id . ' · ' . Str::limit($modelo->tipo_audiencia, 45);
        }

        if ($modelo instanceof Tarea) {
            return 'Tarea #' . $modelo->id . ' · ' . Str::limit($modelo->titulo, 45);
        }

        if ($modelo instanceof Consulta) {
            return 'Consulta #' . $modelo->id;
        }

        return class_basename($modelo) . ' #' . $modelo->getKey();
    }
}
