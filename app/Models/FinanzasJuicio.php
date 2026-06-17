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
        'notas_acuerdo'
    ];

    public function pagos()
    {
        return $this->hasMany(PagosJuicio::class, 'finanzas_juicios_id');
    }
    public function juicio()
    {
        return $this->belongsTo(Juicio::class, 'juicio_id');
    }
    //accesors para total pagado
    public function getTotalPagadoAttribute(){
        return $this->pagos()->where('estado','Aprobado')->sum('monto');
    }

    // sccesror para calcular el saldo pendiente
    public function getSaldoAttribute(){
        $totalDeuda =  $this->honorarios_totales + $this->gastos_extras;
        return $totalDeuda - $this->total_pagado;  
    }

}
