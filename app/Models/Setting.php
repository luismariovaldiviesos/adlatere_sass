<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'razonSocial','nombreComercial','ruc','estab','ptoEmi','dirMatriz',
        'dirEstablecimiento','telefono','email','ambiente','tipoEmision',
        'contribuyenteEspecial','obligadoContabilidad', 'cert_file','cert_password','logo','leyend',
        'printer','annulment_days', 'enable_caja', 'secuencial_factura', 'rimpe_type', 'agente_retencion',
        'firma_actividades_modo', 'firma_actividades_mismo_certificado',
        'firma_actividades_cert_file', 'firma_actividades_cert_password' ];

    public function getCertPasswordAttribute($value)
    {
        return $this->descifrarPassword($value);
    }

    public function setCertPasswordAttribute($value): void
    {
        $this->attributes['cert_password'] = $this->cifrarPassword($value);
    }

    public function getFirmaActividadesCertPasswordAttribute($value)
    {
        return $this->descifrarPassword($value);
    }

    public function setFirmaActividadesCertPasswordAttribute($value): void
    {
        $this->attributes['firma_actividades_cert_password'] = $this->cifrarPassword($value);
    }

    private function cifrarPassword($value): ?string
    {
        return ($value === null || $value === '') ? null : Crypt::encryptString($value);
    }

    private function descifrarPassword($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public static function isCajaEnabled(): bool
    {
        return Cache::remember('setting_enable_caja', 3600, fn() => 
            (bool) (self::first()?->enable_caja ?? false)
        );
    }
}
