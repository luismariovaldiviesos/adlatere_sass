<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanzasJuicio extends Model
{
    use HasFactory;
    protected $table = 'finanzas_juicios';
    protected $fillable = [
        'juicio_id',
        'honorarios_totales',
        'gastos_extras',
        'notas_acuerdo',
        'estado',
    ];

    public function pagos()
    {
        return $this->hasMany(PagosJuicio::class, 'finanzas_juicios_id');
    }
    public function juicio()
    {
        return $this->belongsTo(Juicio::class, 'juicio_id');
    }
}
