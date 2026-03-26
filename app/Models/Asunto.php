<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asunto extends Model
{
    use HasFactory;
     protected $fillable = ['nombre','procedimiento_id'];

    public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'nombre' => 'required|unique:asuntos',
                'procedimiento_id' => 'required'
            ];
        } else {
            return [
                'nombre' => "required|unique:asuntos,nombre,{$id}",
                'procedimiento_id' => 'required'
            ];
        }
    }

     public static $messages = [
        'nombre.required' => 'Nombre asunto requerido',
        'nombre.unique' => ' Asunto ya existe en sistema',
        'procedimiento_id.required' => 'Procedimiento requerido',
    ];


    public function procedimiento()
    {
        return $this->belongsTo(Procedimiento::class);
    }

    // public function juicios()
    // {
    //     return $this->hasMany(Juicio::class);
    // }
}
