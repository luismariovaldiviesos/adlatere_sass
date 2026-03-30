<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;
    protected $table = 'especialidades';
    protected $fillable = ['nombre', 'materia_id', 'descripcion'];

     public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'nombre' => 'required|unique:especialidades',
                'materia_id' => 'required'
            ];
        } else {
            return [
                'nombre' => "required|unique:especialidades,nombre,{$id}",
                'materia_id' => 'required'
            ];
        }
    }

    public static $messages = [
        'nombre.required' => 'Nombre especialidad requerido',
        'nombre.unique' => 'La especialidad ya existe en sistema',
        'materia_id.required' => 'Materia especialidad requerido',
    ];


    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

   

}
