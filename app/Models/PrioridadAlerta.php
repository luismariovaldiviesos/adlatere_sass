<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PrioridadAlerta extends Model
{
    use HasFactory;

    protected $fillable = ['prioridad', 'dias_verde', 'dias_amarillo', 'dias_rojo', 'mensaje', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    protected static function booted()
    {
        static::saved(fn() => Cache::forget('prioridad_alertas'));
        static::deleted(fn() => Cache::forget('prioridad_alertas'));
    }

    public static function config()
    {
        return Cache::remember('prioridad_alertas', 3600, function () {
            return static::where('activo', true)->get()->keyBy('prioridad');
        });
    }
}
