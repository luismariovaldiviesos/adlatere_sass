<?php

namespace App\Services;

use App\Models\Setting;

class EmpresaConfiguracionService
{
    public const MENSAJE_INCOMPLETA = 'Para poder facturar debes configurar los datos de la empresa antes (razón social, RUC, dirección, ambiente SRI y firma).';

    public const CAMPOS = [
        'razonSocial' => 'razón social',
        'nombreComercial' => 'nombre comercial',
        'ruc' => 'RUC',
        'estab' => 'establecimiento',
        'ptoEmi' => 'punto de emisión',
        'dirMatriz' => 'dirección',
        'dirEstablecimiento' => 'dirección del establecimiento',
        'telefono' => 'teléfono',
        'email' => 'correo',
        'ambiente' => 'ambiente SRI',
        'tipoEmision' => 'tipo de emisión',
        'contribuyenteEspecial' => 'contribuyente especial',
        'obligadoContabilidad' => 'obligado a llevar contabilidad',
    ];

    public function empresa(): array
    {
        $settings = Setting::first();

        if (!$settings) {
            return [
                'ok' => false,
                'settings' => null,
                'faltantes' => array_values(self::CAMPOS),
                'message' => self::MENSAJE_INCOMPLETA,
            ];
        }

        $faltantes = [];

        foreach (self::CAMPOS as $campo => $etiqueta) {
            if (!filled($settings->{$campo})) {
                $faltantes[] = $etiqueta;
            }
        }

        return [
            'ok' => empty($faltantes),
            'settings' => $settings,
            'faltantes' => $faltantes,
            'message' => empty($faltantes)
                ? 'Datos de la empresa configurados.'
                : self::MENSAJE_INCOMPLETA,
        ];
    }

    public function completa(): bool
    {
        return $this->empresa()['ok'];
    }
}
