<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $casts = [
        'pending_data' => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'suscription_type',
            'amount',
            'bill_date',
            'status',
            'pending_data',
        ];
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(PaymentHistory::class)->latestOfMany();
    }

    /**
     * Cuenta facturas emitidas desde el último pago registrado.
     */
    public function getCurrentCycleInvoiceCount(): int
    {
        $lastPayment = $this->latestPayment;
        $startDate = $lastPayment ? $lastPayment->created_at : $this->created_at;

        return $this->run(function () use ($startDate) {
            return \App\Models\Factura::where('created_at', '>=', $startDate)
                ->whereNotNull('numeroAutorizacion')
                ->count();
        });
    }

    /**
     * Retorna el límite de facturas según el plan.
     */
    public function getInvoiceLimit(): ?int
    {
        // El plan está en la BD central, podemos acceder directamente
        // 1. Intentar match exacto
        $plan = \App\Models\Plan::where('name', $this->suscription_type)->first();
        
        // 2. Si no encuentra, intentar insensitive (MySQL usually is, but let's be sure)
        if (!$plan) {
             $plan = \App\Models\Plan::where('name', 'LIKE', $this->suscription_type)->first();
        }

        // 3. Fallback: Intentar limpiar acentos y mayúsculas si es necesario
        // Pero para "BáSICO" vs "BASICO", un LIKE suele bastar en configuraciones estándar.
        // Si aún así falla, retornamos el default (que podría ser el plan gratuito 0, o 30 si es una corrección hardcoded temporal)
        
        // FIX: Si el plan es "GRATUITO" o "BASICO" y no está en BD, asumimos 30?
        // No, mejor confiar en la BD. Si retorna null, la vista mostrará infinito.
        
        return $plan ? $plan->invoice_limit : null;
    }
}
