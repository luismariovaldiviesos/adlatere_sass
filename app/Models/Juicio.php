<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juicio extends Model
{
    use HasFactory;
    protected $fillable = ['cod_satje', 'asunto_id','estado_procesal_id', 'fecha_inicio', 'prioridad', 'unidad_id'];

    public static function rules($id){
       if($id <=0 ){
            return [
               'cod_satje' => 'required|unique:juicios',
                'asunto_id' => 'required|exists:asuntos,id',
                'unidad_id' => 'required|exists:unidads,id',
                'estado_procesal_id' => 'required|exists:estados_procesales,id',
                'fecha_inicio' => 'required|date',
                
            ];
        }

        else{
            return [
                'cod_satje' => "required|unique:juicios,cod_satje,{$id}",
                'asunto_id' => "required|exists:asuntos,id",
                'unidad_id' => "required|exists:unidads,id",
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
            'unidad_id.required' => 'La Unidad Judicial es obligatoria.',
            'unidad_id.exists' => 'La Unidad Judicial seleccionada no es válida.',
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

    public function unidadJudicial(){
        return $this->belongsTo(\App\Models\Unidad::class, 'unidad_id');
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

    public function actividades(){
        return $this->hasMany(Actividad::class);
    }

    public function audiencias(){
        return $this->hasMany(Audiencia::class);
    }

    public function historialEstados(){
        return $this->hasMany(JuicioHistorialEstado::class)->orderBy('created_at', 'desc');
    }

    public function documentos(){
        return $this->hasMany(Documento::class);
    }

    public function finanza(){
        return $this->hasOne(FinanzasJuicio::class, 'juicio_id');
    }

    public function funcionarios (){
        return $this->belongsToMany(\App\Models\Funcionario::class, 'juicio_funcionario')
                    ->withPivot('rol_en_juicio') // para acceder al rol del funcionario en el juicio
                    ->withTimestamps();
    }
    
}
