<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Procedimiento extends Model
{
    use HasFactory;

    
     protected $fillable = ['nombre','materia_id'];

    public static function rules($id, $materia_id)
    {
        if ($id <= 0) {
            return [
               'materia_id' => 'required',
               'nombre'=> [
                'required',
                Rule::unique('procedimientos')->where(function ($query) use ($materia_id) {
                    return $query->where('materia_id', $materia_id);
                }),
               ],
            ];
        } else {
            return [
                'materia_id' => 'required',
                'nombre'=> [
                    'required',
                    Rule::unique('procedimientos')->where(function ($query) use ($materia_id) {
                        return $query->where('materia_id', $materia_id);
                    })->ignore($id),
                ],
            ];
        }
    }

     public static $messages = [
        'nombre.required' => 'Nombre procedimiento requerido',
        'nombre.unique' => ' Procedimiento ya existe en sistema',
        'materia_id.required' => 'Materia requerida',
    ];


    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function asuntos()
    {
        return $this->hasMany(Asunto::class);
    }
}
