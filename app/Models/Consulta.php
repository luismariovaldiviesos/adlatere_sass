<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'asunto_id', 'abogado_id', 'costo',
        'estado_pago', 'estado_atencion', 'notas',
        'factura_id', 'juicio_id', 'fecha_atencion',
        'fecha_pago', 'metodo_pago', 'comprobante_ruta', 'requiere_factura'
    ];

    protected $casts = [
        'costo' => 'decimal:2',
        'fecha_atencion' => 'datetime',
        'fecha_pago' => 'date',
    ];

    public static function rules($id = 0)
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'asunto_id'   => 'required|exists:asuntos,id',
            'abogado_id'  => 'nullable|exists:users,id',
            'costo'       => 'required|numeric|min:0',
            'notas'       => 'nullable|string',
            'estado_pago' => 'nullable|in:pendiente,pagada,en_facturacion,facturada,no_factura',
            'fecha_pago'  => 'nullable|date',
            'metodo_pago' => 'nullable|string|max:30',
        ];
    }

    public static $messages = [
        'customer_id.required' => 'Seleccione un cliente.',
        'customer_id.exists'   => 'El cliente seleccionado no es válido.',
        'asunto_id.required'   => 'Seleccione el asunto.',
        'asunto_id.exists'     => 'El asunto seleccionado no es válido.',
        'abogado_id.exists'    => 'El abogado seleccionado no es válido.',
        'costo.required'       => 'Indique el costo de la consulta.',
        'costo.numeric'        => 'El costo debe ser numérico.',
        'costo.min'            => 'El costo no puede ser negativo.',
        'estado_pago.in'       => 'Condición de pago no válida.',
        'fecha_pago.date'      => 'Fecha de pago no válida.',
    ];

    public static function notasTienenTexto($html)
    {
        $texto = html_entity_decode(strip_tags($html ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = str_replace("\xC2\xA0", ' ', $texto);

        return trim($texto) !== '';
    }

    public function getTieneNotasAttribute()
    {
        return static::notasTienenTexto($this->notas);
    }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function asunto() { return $this->belongsTo(Asunto::class); }
    public function abogado() { return $this->belongsTo(User::class, 'abogado_id'); }
    public function juicio() { return $this->belongsTo(Juicio::class); }
    public function factura() { return $this->belongsTo(Factura::class); }

    public function estaBloqueada(): bool
    {
        return in_array($this->estado_atencion, ['atendida', 'convertida'], true)
            || (bool) $this->factura_id
            || in_array($this->estado_pago, ['en_facturacion', 'facturada'], true);
    }

    public function scopePendientes($q) { return $q->where('estado_atencion', 'pendiente'); }
    public function scopeAtendidas($q) { return $q->where('estado_atencion', 'atendida'); }
}
