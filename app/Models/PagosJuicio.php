<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagosJuicio extends Model
{
    protected  $table = 'pagos_juicios';
    protected $fillable = [
        'finanzas_juicios_id',
        'user_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'comprobante_ruta',
        'estado',
        'notas',
    ];
    use HasFactory;
}
