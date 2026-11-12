<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fase extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'materia_id'];
    protected $table = 'fase';

    public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'nombre' => 'required|min:3|max:50|unique:fase',
                'materia_id' => 'required|exists:materias,id',
            ];
        }

        return [
            'nombre' => "required|min:3|max:50|unique:fase,nombre,{$id}",
            'materia_id' => 'required|exists:materias,id',
        ];
    }


    public static $messages = [
        'nombre.required' => 'Nombre requerido',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
        'nombre.max' => 'El nombre debe tener máximo 50 caracteres',
        'nombre.unique' => 'La fase procesal ya existe en sistema',
        'materia_id.required' => 'Seleccione la materia de la fase procesal.',
        'materia_id.exists' => 'La materia seleccionada no es válida.',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }


    public function estados_procesales()
    {
        return $this->hasMany(EstadoProcesal::class);
    }
}
