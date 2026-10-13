<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TareaAdjunto extends Model
{
    use HasFactory;

    protected $fillable = [
        'tarea_id', 'user_id', 'nombre', 'ruta_archivo', 'tipo_archivo', 'tamaño_archivo',
    ];

    public function tarea() { return $this->belongsTo(Tarea::class); }
    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
}
