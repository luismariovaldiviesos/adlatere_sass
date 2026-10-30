<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory, HasDocumentos;
    protected $table = 'actividades';

    protected $fillable = [
        'juicio_id',
        'tipo_actividad_id',
        'user_id',
        'origen',
        'fecha_actividad',
        'descripcion',
        'contenido',
        'archivo', 'estado_firma', 'firma_origen', 'firma_titular',
        'firmado_por_user_id', 'firmado_en',
        'pdf_original_path', 'pdf_firmado_path', 'firma_hash', 'firma_metadatos'
        
    ];
     protected $casts = [
            'fecha_actividad' => 'datetime',
            'firma_metadatos' => 'array',
    ];

    protected static function booted()
    {
        static::saved(fn($a) => \App\Models\Juicio::recalcularUltimaActividad($a->juicio_id));
        static::deleted(fn($a) => \App\Models\Juicio::recalcularUltimaActividad($a->juicio_id));
    }

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

   public function firmadoPor(){
        return $this->belongsTo(User::class, 'firmado_por_user_id');
   }

   public function scopePendientesFirma($query) {
        return $query->where('estado_firma', 'pendiente');
   }

    public function scopeFirmadas($query) {
        return $query->where('estado_firma', 'firmada');
    }

    public function nombrePdf(): string
    {
        $this->loadMissing(['juicio.clientes', 'juicio.actores', 'juicio.demandados', 'tipoActividad']);

        $base = $this->juicio->cod_satje ?? null;

        if (!$base && $this->juicio) {
            $cliente = $this->juicio->clientes->first()
                ?: $this->juicio->actores->first()
                ?: $this->juicio->demandados->first();
            $base = $cliente->businame ?? null;
        }

        $base = $base ?: ($this->tipoActividad->nombre ?? 'actividad');
        $fecha = $this->firmado_en
            ? \Carbon\Carbon::parse($this->firmado_en)
            : ($this->created_at ?: now());

        return \Illuminate\Support\Str::slug($base . '_' . $fecha->format('Y-m-d')) . '.pdf';
    }

   public function juicio(){
        return $this->belongsTo(Juicio::class, 'juicio_id');
   }

   public function tarea(){
        return $this->hasOne(Tarea::class, 'actividad_id');
   }

       public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

  
}
