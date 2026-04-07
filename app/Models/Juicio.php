<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juicio extends Model
{
    use HasFactory;
    protected $fillable = ['cod_satje', 'asunto_id','estado_procesal_id', 'fecha_inicio', 'prioridad'];




    public function asunto(){
        return $this->belongsTo(Asunto::class);
    }
}
