<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class CertificadoFacturacionService
{
    public function estado(): array
    {
        $settings = Setting::first();

        if (!$settings) {
            return [
                'ok' => false,
                'message' => 'No hay datos de empresa configurados.',
                'expires_at' => null,
            ];
        }

        if (empty($settings->cert_file)) {
            return [
                'ok' => false,
                'message' => 'No se ha cargado un certificado de facturación.',
                'expires_at' => null,
            ];
        }

        if (empty($settings->cert_password)) {
            return [
                'ok' => false,
                'message' => 'El certificado no tiene contraseña configurada.',
                'expires_at' => null,
            ];
        }

        $path = Storage::disk('certificados')->path($settings->cert_file);

        if (!is_file($path)) {
            return [
                'ok' => false,
                'message' => 'El archivo del certificado no existe en el servidor.',
                'expires_at' => null,
            ];
        }

        $pkcs12 = @file_get_contents($path);
        $certs = [];

        if (!$pkcs12 || !@openssl_pkcs12_read($pkcs12, $certs, $settings->cert_password)) {
            return [
                'ok' => false,
                'message' => 'La contraseña del certificado es incorrecta o el archivo está dañado.',
                'expires_at' => null,
            ];
        }

        $parsed = @openssl_x509_parse($certs['cert'] ?? '');

        if (!$parsed) {
            return [
                'ok' => false,
                'message' => 'No se pudo leer la información del certificado.',
                'expires_at' => null,
            ];
        }

        $validTo = isset($parsed['validTo_time_t'])
            ? (int) $parsed['validTo_time_t']
            : 0;

        if ($validTo > 0 && $validTo < now()->timestamp) {
            return [
                'ok' => false,
                'message' => 'El certificado está vencido desde el ' . date('d/m/Y', $validTo) . '.',
                'expires_at' => $validTo,
            ];
        }

        return [
            'ok' => true,
            'message' => $validTo > 0
                ? 'Certificado válido hasta el ' . date('d/m/Y', $validTo) . '.'
                : 'Certificado válido.',
            'expires_at' => $validTo ?: null,
        ];
    }
}
