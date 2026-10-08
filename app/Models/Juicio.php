<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juicio extends Model
{
    use HasFactory;
    protected $fillable = ['cod_satje', 'asunto_id','estado_procesal_id', 'fecha_inicio', 'prioridad', 'unidad_id'];

    protected $casts = [
        'ultima_actividad_at' => 'datetime',
    ];

    // Recalcula el último movimiento (lo llaman los observers de
    // Actividad/Audiencia/Documento; sin esto no hay semáforo fiable)
    public static function recalcularUltimaActividad($juicioId)
    {
        $j = static::find($juicioId);
        if (!$j) return;
        $ultAct = \App\Models\Actividad::where('juicio_id', $juicioId)->max('fecha_actividad');
        $ultAud = \App\Models\Audiencia::where('juicio_id', $juicioId)->max('fecha_hora');
        $ultDoc = \App\Models\Documento::where('juicio_id', $juicioId)->max('created_at');
        $max = collect([$ultAct, $ultAud, $ultDoc])->filter()->map(fn($f) => \Carbon\Carbon::parse($f))->max();
        $j->ultima_actividad_at = $max ?: $j->fecha_inicio;
        $j->saveQuietly(); // sin disparar eventos (evita loops)
    }

    protected static function booted()
    {
        // Un juicio nuevo nace con el reloj en su fecha de inicio
        static::creating(function ($j) {
            $j->ultima_actividad_at = $j->ultima_actividad_at ?: ($j->fecha_inicio ?? now());
        });
    }

    // Semáforo de alertas: ['color' => green|yellow|red|gray, 'dias', 'hex', 'texto']
    public function getSemaforoAttribute()
    {
        $cfg = \App\Models\PrioridadAlerta::config();
        $base = $this->ultima_actividad_at ?: $this->fecha_inicio;
        $dias = $base ? \Carbon\Carbon::parse($base)->diffInDays(now()) : 0;
        $c = $cfg[$this->prioridad] ?? null;
        if (!$c) {
            return ['color' => 'gray', 'dias' => $dias, 'hex' => '#9ca3af',
                'texto' => $dias . ' días sin movimiento (sin configuración)'];
        }
        if ($dias <= $c->dias_verde) {
            $hex = '#22c55e'; $nivel = 'verde';
        } elseif ($dias <= $c->dias_amarillo) {
            $hex = '#eab308'; $nivel = 'amarillo';
        } else {
            $hex = '#ef4444'; $nivel = 'rojo';
        }
        $texto = $dias . ' días sin movimiento · ' . $this->prioridad . ' en ' . $nivel . ' (rojo a los ' . $c->dias_rojo . ')';
        if (!empty($c->mensaje)) {
            $texto .= ' · ' . $c->mensaje;
        }
        return ['color' => $nivel, 'dias' => $dias, 'hex' => $hex, 'texto' => $texto];
    }

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
                    ->withPivot('rol', 'es_cliente') // rol + marca de cliente del despacho
                    ->withTimestamps();
    }

    // Clientes del despacho en este juicio (pueden ser actores, demandados o ambos)
    public function clientes(){
        return $this->participantes()->wherePivot('es_cliente', true);
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
    public function abogados(){
        return $this->belongsToMany(\App\Models\User::class, 'juicio_user')
                    ->withPivot('rol_en_juicio') // para acceder al rol del funcionario en el juicio
                    ->withTimestamps();
    }
    
}
