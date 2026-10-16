<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

     protected $fillable = ['nombre'];

    public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'nombre' => 'required|unique:materias',
            ];
        } else {
            return [
                'nombre' => "required|unique:materias,nombre,{$id}",
            ];
        }
    }

     public static $messages = [
        'nombre.required' => 'Nombre materia requerido',
        'nombre.unique' => ' Materia ya existe en sistema',
    ];


    public function procedimientos()
    {
        return $this->hasMany(Procedimiento::class);
    }
        public function especialidades()
        {
            return $this->hasMany(Especialidad::class);
        }
}
