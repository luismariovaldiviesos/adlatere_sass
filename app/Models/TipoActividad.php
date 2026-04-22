<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoActividad extends Model
{
    use HasFactory;
    protected $table = 'tipo_actividad';
    protected $fillable = ['nombre'];


    public static function rules($id){
        if($id <=0 ){
            return [
                'nombre' => 'required|min:3|unique:tipo_actividad'
            ];
        }

        else{
            return [
                'nombre' => "required|min:3|string|unique:tipo_actividad,nombre,{$id}"
            ];

        }
    }

    public static   $messages =[
        'nombre.required' => 'nombre requerido',
        'nombre.min' => 'nombre debe tener al menos 3 caracteres',
        'nombre.unique' => 'nombre ya esta en uso'
    ];


    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'tipo_actividad_id');
    }

}
