<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audiencia extends Model
{
    use HasFactory;
    protected $fillable = [
        'juicio_id',
        'fecha_hora',
        'tipo_audiencia',
        'sala_enlace',
        'estado',
        'acta_resumen',
    ];

    protected function rules($id)
    {
        if ($id <= 0) {
            return [
                'juicio_id' => 'required|exists:juicios,id',
                'fecha_hora' => 'required|date',
                'tipo_audiencia' => 'required|string|max:255',
                'sala_enlace' => 'required|string|max:255',
                'estado' => 'required|string|in:Programada,Realizada,Suspendida',
                'acta_resumen' => 'nullable|string',
            ];
        } else {
            return [
                'juicio_id' => "required|exists:juicios,id",
                'fecha_hora' => 'required|date',
                'tipo_audiencia' => 'required|string|max:255',
                'sala_enlace' => 'required|string|max:255',
                'estado' => 'required|string|in:Programada,Realizada,Suspendida',
                'acta_resumen' => 'nullable|string',
            ];
        }
    }

    public static $messages = [
        'juicio_id.required' => 'El juicio es requerido',
        'juicio_id.exists' => 'El juicio seleccionado no existe',
        'fecha_hora.required' => 'La fecha y hora de la audiencia es requerida',
        'fecha_hora.date' => 'La fecha y hora debe ser una fecha válida',
        'tipo_audiencia.required' => 'El tipo de audiencia es requerido',
        'tipo_audiencia.string' => 'El tipo de audiencia debe ser una cadena de texto',
        'tipo_audiencia.max' => 'El tipo de audiencia no debe exceder los 255 caracteres',
        'sala_enlace.required' => 'La sala o enlace es requerido',
        'sala_enlace.string' => 'La sala o enlace debe ser una cadena de texto',
        'sala_enlace.max' => 'La sala o enlace no debe exceder los 255 caracteres',
        'estado.required' => 'El estado de la audiencia es requerido',
        'estado.string' => 'El estado de la audiencia debe ser una cadena de texto',
        'estado.in' => 'El estado de la audiencia debe ser Programada, Realizada o Suspendida',
        'acta_resumen.string' => 'El acta resumen debe ser una cadena de texto',
    ];

    public function juicio()
    {
        return $this->belongsTo(Juicio::class);
    }
}
