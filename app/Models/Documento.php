<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;
    protected $fillable = [
        'juicio_id',
        'origen_tipo',
        'origen_id',
        'nombre',
        'ruta_archivo',
        'tipo_archivo',
        'tamaño_archivo',
    ];

    public function juicio()
    {
        return $this->belongsTo(Juicio::class);
    }
}
