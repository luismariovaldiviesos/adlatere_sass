<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagosJuicio extends Model
{
    protected  $table = 'pagos_juicios';

     protected $fillable = [
        'finanzas_juicios_id', 'customer_id', 'user_id', 'monto', 'fecha_pago', 'metodo_pago', 'referencia_transaccion', 'comprobante_ruta', 'estado', 'notas', 'factura_id'
    ];
    use HasFactory;

    public function finanza()
    {
        return $this->belongsTo(FinanzasJuicio::class, 'finanzas_juicios_id');
    }

    Public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function cliente()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function factura()
    {
        return $this->belongsTo(\App\Models\Factura::class, 'factura_id');
    }
}
