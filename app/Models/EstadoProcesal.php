<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoProcesal extends Model
{
    use HasFactory;
     protected $fillable = ['nombre','descripcion'];
     protected $table = 'estados_procesales';

    public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'nombre' => 'required|min:3|max:50|unique:estados_procesales'
            ];
        } else {
            return [
                'nombre' => "required|min:3|max:50|unique:estados_procesales,nombre,{$id}"
            ];
        }
    }


    public static $messages = [
        'nombre.required' => 'Nombre requerido',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
        'nombre.max' => 'El nombre debe tener máximo 50 caracteres',
        'nombre.unique' => 'El estado procesal ya existe en sistema'
    ];


    public function fase()
    {
        return $this->belongsTo(Fase::class);
    }
}
