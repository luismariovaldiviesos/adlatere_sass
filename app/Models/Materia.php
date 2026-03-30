<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

     protected $fillable = ['nombre','unidad_id'];

    public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'nombre' => 'required|unique:materias',
                'unidad_id' => 'required'
            ];
        } else {
            return [
                'nombre' => "required|unique:materias,nombre,{$id}",
                'unidad_id' => 'required'
            ];
        }
    }

     public static $messages = [
        'nombre.required' => 'Nombre materia requerido',
        'nombre.unique' => ' Materia ya existe en sistema',
        'unidad_id.required' => 'Unidad requerida',
    ];


    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

     public function procedimientos()
    {
        return $this->hasMany(Procedimiento::class);
    }
        public function especialidades()
        {
            return $this->hasMany(Especialidad::class);
        }
}
