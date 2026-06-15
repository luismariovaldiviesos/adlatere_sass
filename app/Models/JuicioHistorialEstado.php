<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuicioHistorialEstado extends Model
{
    use HasFactory;
    
    // Le decimos a Laravel que esta tabla no usa la columna updated_at
    const UPDATED_AT = null;
    
    protected $table = 'juicio_historial_estados';

    protected $fillable = [
        'juicio_id',
        'user_id',
        'estado_procesal_id',
        'tipo_movimiento',
        'referencia_tipo',
        'referencia_id',
        'descripcion',
    ];

    //protegemos el modelo para que no se pueda modificar el historial, solo agregar nuevos registros
    public function save(array $options = []){
        if ($this->exists) {
            throw new \Exception("El historial del juicio es inmutable y no puede ser modificado.");
        }
        return parent::save($options);
    }

    public static function boot()
    {
        parent::boot();

        // Evitar actualizaciones: si el modelo ya existe, no permitir cambios
        static::deleting(function () {
            throw new \Exception("El historial del juicio es inmutable y no puede ser eliminado.");
        });
    }


    public function juicio(){
        return $this->belongsTo(Juicio::class, 'juicio_id');
    }

    public function estadoProcesal(){
        return $this->belongsTo(EstadoProcesal::class, 'estado_procesal_id');
    }

    public function usuario(){
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
