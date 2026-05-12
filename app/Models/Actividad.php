<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;
    protected $table = 'actividades';

    protected $fillable = [
        'juicio_id',
        'tipo_actividad_id',
        'user_id',
        'origen',
        'fecha_actividad',
        'descripcion',
        'contenido',
        'archivo',
    ];

   public static function rules($id){
        if($id <= 0){
            return [
                'juicio_id' => 'required|exists:juicios,id',
                'tipo_actividad_id' => 'required|exists:tipos_actividades,id',
                'user_id' => 'required|exists:users,id',
                'origen' => 'required|in:Interno,Externo',
                'fecha_actividad' => 'required|date',
                'descripcion' => 'nullable|string',
                'contenido' => 'required|string',
                'archivo' => 'nullable|file|mimes:pdf,doc,docx,txt|max:5120',
            ];
        } else {
            return [
                'juicio_id' => "required|exists:juicios,id",
                'tipo_actividad_id' => "required|exists:tipos_actividades,id",
                'user_id' => "required|exists:users,id",
                'origen' => "required|in:Interno,Externo",
                'fecha_actividad' => "required|date",
                'descripcion' => "nullable|string",
                'contenido' => "required|string",
                'archivo' => "nullable|file|mimes:pdf,doc,docx,txt|max:5120",
            ];
        }
   }

   public static $messages = [
        'juicio_id.required' => 'Juicio requerido',
        'juicio_id.exists' => 'Juicio no válido',
        'tipo_actividad_id.required' => 'Tipo de actividad requerido',
        'tipo_actividad_id.exists' => 'Tipo de actividad no válido',
        'user_id.required' => 'Usuario requerido',
        'user_id.exists' => 'Usuario no válido',
        'origen.required' => 'Origen requerido',
        'origen.in' => 'Origen debe ser Interno o Externo',
        'fecha_actividad.required' => 'Fecha de actividad requerida',
        'fecha_actividad.date' => 'Fecha de actividad no válida',
        'descripcion.string' => 'Descripción debe ser texto',
        'contenido.required' => 'Contenido requerido',
        'contenido.string' => 'Contenido debe ser texto',
        'archivo.file' => 'Archivo debe ser un archivo válido',
        'archivo.mimes' => 'Archivo debe ser un PDF, DOC, DOCX o TXT',
        'archivo.max' => 'Archivo debe tener máximo 5 MB',
   ];

   public function tipoActividad(){
        return $this->belongsTo(TipoActividad::class, 'tipo_actividad_id');
   }
}
