<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'razonSocial','nombreComercial','ruc','estab','ptoEmi','dirMatriz',
        'dirEstablecimiento','telefono','email','ambiente','tipoEmision',
        'contribuyenteEspecial','obligadoContabilidad', 'cert_file','cert_password','logo','leyend',
        'printer','annulment_days', 'enable_caja', 'secuencial_factura', 'rimpe_type', 'agente_retencion' ];

    public static function isCajaEnabled(): bool
    {
        return Cache::remember('setting_enable_caja', 3600, fn() => 
            (bool) (self::first()?->enable_caja ?? false)
        );
    }
}
