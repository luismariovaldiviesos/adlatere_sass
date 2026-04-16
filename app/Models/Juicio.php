<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juicio extends Model
{
    use HasFactory;
    protected $fillable = ['cod_satje', 'asunto_id','estado_procesal_id', 'fecha_inicio', 'prioridad'];

    public static function rules($id){
       if($id <=0 ){
            return [
               'cod_satje' => 'required|unique:juicios',
                'asunto_id' => 'required|exists:asuntos,id',
                'estado_procesal_id' => 'required|exists:estados_procesales,id',
                'fecha_inicio' => 'required|date',
                
            ];
        }

        else{
            return [
                'cod_satje' => "required|unique:juicios,cod_satje,{$id}",
                'asunto_id' => "required|exists:asuntos,id",
                'estado_procesal_id' => "required|exists:estados_procesales,id",
                'fecha_inicio' => 'required|date',
                'prioridad' => 'required|in:Baja,Media,Alta,Urgente'
            ];

        }
    }

    public static function messages(){
        return [
            'cod_satje.required' => 'El código SATJE es obligatorio.',
            'cod_satje.unique' => 'El código SATJE ya existe. Por favor, ingrese uno diferente.',
            'asunto_id.required' => 'El asunto es obligatorio.',
            'asunto_id.exists' => 'El asunto seleccionado no es válido.',
            'estado_procesal_id.required' => 'El estado procesal es obligatorio.',
            'estado_procesal_id.exists' => 'El estado procesal seleccionado no es válido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'prioridad.required' => 'La prioridad es obligatoria.',
            'prioridad.in' => 'La prioridad debe ser una de las siguientes: Baja, Media, Alta, Urgente.'
        ];
    }




    public function asunto(){
        return $this->belongsTo(Asunto::class);
    }

    public function estadoProcesal(){
        return $this->belongsTo(EstadoProcesal::class, 'estado_procesal_id');
    }

    //relacion principal con participantes (clientes)
    public function participantes(){
        return $this->belongsToMany(Customer::class, 'juicio_participante')
                    ->withPivot('rol') // para acceder al rol del participante en el juicio
                    ->withTimestamps();
    }

    public function actores (){
        return $this->participantes()->wherePivot('rol', 'actor');
    }
    public function demandados (){
        return $this->participantes()->wherePivot('rol', 'demandado');
    }

    
}
