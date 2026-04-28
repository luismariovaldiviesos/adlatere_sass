<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantillaTipoActividad extends Model
{
    use HasFactory;
    protected $table = 'plantillas_tipos_actividad';
    protected $fillable = [
        'nombre',
        'tipo_actividad_id',
        'contenido',
        'activo',
    ];

    public static function rules($id){
        if($id <=0 ){
            return [
                'nombre' => 'required|min:3|unique:plantillas_tipos_actividad',
                'tipo_actividad_id' => 'required|exists:tipo_actividad,id',
                'contenido' => 'required',
            ];
        }

        else{
            return [
                'nombre' => "required|min:3|string|unique:plantillas_tipos_actividad,nombre,{$id}",
                'tipo_actividad_id' => 'required|exists:tipo_actividad,id',
                'contenido' => 'required',
            ];

        }
    }

    public static   $messages =[
        'nombre.required' => 'nombre requerido',
        'nombre.min' => 'nombre debe tener al menos 3 caracteres',
        'nombre.unique' => 'nombre ya esta en uso',
        'tipo_actividad_id.required' => 'tipo de actividad requerido',
        'tipo_actividad_id.exists' => 'tipo de actividad no existe',
        'contenido.required' => 'contenido requerido',
    ];

    public function tipoActividad()
    {
        return $this->belongsTo(TipoActividad::class, 'tipo_actividad_id');
    }
}
