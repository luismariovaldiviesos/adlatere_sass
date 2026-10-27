<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Documento;
use App\Models\JuicioHistorialEstado;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Tcpdf\Fpdi;

class ActividadSignatureService
{
    public const ORIGEN_PERSONAL = 'personal';
    public const ORIGEN_DESPACHO = 'despacho';

    public function opcionesFirma(Actividad $actividad, User $operador, ?string $personalPath = null): array
    {
        $actividad->loadMissing(['tipoActividad', 'juicio.abogados']);

        $config = Setting::first();
        $modo = $this->modoFirma($config);
        $esAdmin = $this->esAdmin($operador);
        $esPatrocinador = $this->esPatrocinador($actividad, $operador);

        $puedePersonal = $operador->can('firmar_actividad') && ($esAdmin || $esPatrocinador);
        $puedeDespacho = $esAdmin
            || ($esPatrocinador && (bool) $operador->puede_firmar_despacho);

        $personal = $this->infoCertificadoPersonal($operador, $puedePersonal, $personalPath);
        $despacho = $this->infoCertificadoDespacho($config, $puedeDespacho);

        if ($modo === 'automatico') {
            $personal['disponible'] = false;
            $personal['permitido'] = false;
            $personal['motivo'] = 'La política del despacho usa el certificado institucional.';
        }

        $esFirmable = (bool) ($actividad->tipoActividad->es_firmable ?? false);
        $yaFirmada = strtolower((string) $actividad->estado_firma) === 'firmada';
        $puedeFirmar = $esFirmable && !$yaFirmada;

        if ($modo === 'automatico') {
            $puedeFirmar = $puedeFirmar && $despacho['disponible'];
        } else {
            $puedeFirmar = $puedeFirmar && ($personal['permitido'] || $despacho['disponible']);
        }

        if ($modo === 'predeterminado' || $modo === 'automatico') {
            $predeterminado = $despacho['disponible']
                ? self::ORIGEN_DESPACHO
                : (($personal['disponible'] || $personal['permitido']) ? self::ORIGEN_PERSONAL : null);
        } else {
            $predeterminado = $personal['disponible']
                ? self::ORIGEN_PERSONAL
                : ($despacho['disponible']
                    ? self::ORIGEN_DESPACHO
                    : ($personal['permitido'] ? self::ORIGEN_PERSONAL : null));
        }

        $mensaje = null;
        if (!$esFirmable) {
            $mensaje = 'Este tipo de actividad no permite firma electrónica.';
        } elseif ($yaFirmada) {
            $mensaje = 'La actividad ya está firmada.';
        } elseif ($modo === 'automatico' && !$despacho['disponible']) {
            $mensaje = 'La política del despacho exige el certificado institucional, pero no está disponible.';
        } elseif (!$personal['permitido'] && !$despacho['disponible']) {
            $mensaje = $despacho['motivo'] ?: 'No hay un certificado disponible para firmar.';
        }

        return [
            'modo' => $modo,
            'puede_firmar' => $puedeFirmar,
            'mensaje' => $mensaje,
            'predeterminado' => $predeterminado,
            'personal' => $personal,
            'despacho' => $despacho,
        ];
    }

    public function firmar(Actividad $actividad, User $operador, string $origen, string $password, ?string $personalPath = null): array
    {
        $opciones = $this->opcionesFirma($actividad, $operador, $personalPath);

        if (!$opciones['puede_firmar']) {
            throw new \Exception($opciones['mensaje'] ?: 'No puede firmar esta actividad.');
        }

        if (!in_array($origen, [self::ORIGEN_PERSONAL, self::ORIGEN_DESPACHO], true)) {
            throw new \Exception('Seleccione un certificado válido.');
        }

        if ($opciones['modo'] === 'automatico' && $origen !== self::ORIGEN_DESPACHO) {
            throw new \Exception('La política del despacho exige firmar con el certificado institucional.');
        }

        $opcion = $opciones[$origen] ?? null;
        if ($origen === self::ORIGEN_PERSONAL) {
            if (!($opciones['personal']['permitido'] ?? false)) {
                throw new \Exception('No tiene permiso para firmar con su certificado personal.');
            }
            if (empty($personalPath) && empty($operador->firma_path)) {
                throw new \Exception('Seleccione su archivo de certificado personal.');
            }
        } elseif (!$opcion || !$opcion['disponible']) {
            throw new \Exception($opcion['motivo'] ?? 'El certificado seleccionado no está disponible.');
        }

        if ($password === '') {
            throw new \Exception('Ingrese la contraseña del certificado seleccionado.');
        }

        $credencial = $this->resolverCredencial($operador, $origen, $personalPath);
        $certs = $this->cargarPkcs12($credencial['path'], $password);
        $this->validarVigencia($certs['cert']);

        $pdfOriginalPath = $this->generarPdfOriginal($actividad);
        $hashOriginal = hash_file('sha256', Storage::disk('public')->path('actividades/originales/' . $pdfOriginalPath));
        $pdfFirmadoPath = $this->firmarPdfConFirmaVisible($pdfOriginalPath, $certs, $credencial, $operador, $actividad, $hashOriginal);

        $actividad->update([
            'estado_firma' => 'firmada',
            'firma_origen' => $origen,
            'firma_titular' => $certs['titular'],
            'firmado_por_user_id' => $operador->id,
            'firmado_en' => now(),
            'pdf_original_path' => $pdfOriginalPath,
            'pdf_firmado_path' => $pdfFirmadoPath,
            'firma_hash' => $hashOriginal,
            'firma_metadatos' => [
                'origen' => $origen,
                'cert_subject' => $certs['subject'],
                'cert_subject_cn' => $certs['subject_cn'],
                'cert_issuer' => $certs['issuer'],
                'cert_serial' => $certs['serial'],
                'cert_fingerprint' => $certs['fingerprint'],
                'cert_valid_from' => $certs['valid_from'],
                'cert_valid_to' => $certs['valid_to'],
                'titular' => $certs['titular'],
                'operator_user_id' => $operador->id,
                'operator_name' => $operador->name,
                'algorithm' => 'sha256WithRSAEncryption',
                'standard' => 'PAdES-BES',
            ],
        ]);

        $doc = Documento::create([
            'juicio_id' => $actividad->juicio_id,
            'origen_tipo' => 'Actividad',
            'origen_id' => $actividad->id,
            'nombre' => 'Actividad Firmada Electrónicamente - ' . ($actividad->tipoActividad->nombre ?? ''),
            'ruta_archivo' => 'actividades/firmados/' . $pdfFirmadoPath,
            'tipo_archivo' => 'pdf',
            'tamaño_archivo' => round(Storage::disk('public')->size('actividades/firmados/' . $pdfFirmadoPath) / 1024, 2),
        ]);

        $origenTexto = $origen === self::ORIGEN_DESPACHO ? 'certificado del despacho' : 'certificado personal';

        JuicioHistorialEstado::create([
            'juicio_id' => $actividad->juicio_id,
            'user_id' => $operador->id,
            'estado_procesal_id' => $actividad->juicio->estado_procesal_id ?? null,
            'tipo_movimiento' => 'actividad_firmada',
            'referencia_tipo' => 'Actividad',
            'referencia_id' => $actividad->id,
            'descripcion' => "Actividad '{$actividad->tipoActividad->nombre}' firmada digitalmente por {$certs['titular']} con {$origenTexto}; operador: {$operador->name}",
        ]);

        return [
            'documento_id' => $doc->id,
            'pdf_firmado_path' => $pdfFirmadoPath,
            'hash' => $hashOriginal,
            'origen' => $origen,
            'titular' => $certs['titular'],
        ];
    }

    public function pdfBytes(Actividad $actividad): string
    {
        $actividad->loadMissing(['juicio.actores', 'juicio.demandados', 'tipoActividad', 'user']);

        $html = view('pdf.actividad', [
            'actividad' => $actividad,
            'empresa' => Setting::first(),
        ])->render();

        return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'fontDir' => storage_path('fonts'),
                'fontCache' => storage_path('fonts'),
            ])->output();
    }

    private function generarPdfOriginal(Actividad $actividad): string
    {
        $pdfBytes = $this->pdfBytes($actividad);
        $relativePath = $actividad->created_at->format('Y/m/') . "actividad_{$actividad->id}.pdf";

        Storage::disk('public')->put('actividades/originales/' . $relativePath, $pdfBytes);

        return $relativePath;
    }

    private function cargarPkcs12(string $path, string $password): array
    {
        if (!is_file($path)) {
            throw new \Exception('El archivo de certificado no existe en disco.');
        }

        $pkcs12 = file_get_contents($path);
        $certs = [];
        if (!openssl_pkcs12_read($pkcs12, $certs, $password)) {
            throw new \Exception('Error leyendo PKCS#12: contraseña incorrecta o archivo corrupto.');
        }

        $certData = openssl_x509_parse($certs['cert']);
        $subject = $certData['subject'] ?? [];
        $issuer = $certData['issuer'] ?? [];
        $subjectCn = $subject['CN'] ?? ($subject['O'] ?? 'Titular del certificado');

        return [
            'cert' => $certs['cert'],
            'pkey' => $certs['pkey'],
            'extracerts' => $certs['extracerts'] ?? null,
            'serial' => (string) ($certData['serialNumber'] ?? ''),
            'issuer' => $issuer['CN'] ?? ($issuer['O'] ?? ''),
            'subject' => $subject,
            'subject_cn' => $subjectCn,
            'titular' => $subjectCn,
            'valid_from' => $certData['validFrom_time_t'] ?? null,
            'valid_to' => $certData['validTo_time_t'] ?? null,
            'fingerprint' => openssl_x509_fingerprint($certs['cert'], 'sha256') ?: null,
        ];
    }

    private function validarVigencia($cert): void
    {
        $data = openssl_x509_parse($cert);
        if (($data['validTo_time_t'] ?? 0) < time()) {
            throw new \Exception('El certificado digital ha expirado.');
        }
    }

    private function firmarPdfConFirmaVisible(
        string $originalPath,
        array $certs,
        array $credencial,
        User $operador,
        Actividad $actividad,
        string $hashOriginal
    ): string {
        $originalFull = Storage::disk('public')->path('actividades/originales/' . $originalPath);
        $baseName = basename($originalPath, '.pdf');
        $pdfFirmadoName = $baseName . '_firmado_' . time() . '.pdf';
        $relativeDir = dirname($originalPath);
        $outputDir = 'actividades/firmados' . ($relativeDir !== '.' ? '/' . $relativeDir : '');

        Storage::disk('public')->makeDirectory($outputDir);
        $pdfFirmadoFull = Storage::disk('public')->path($outputDir . '/' . $pdfFirmadoName);

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($originalFull);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }

        $info = [
            'Name' => $certs['titular'],
            'Location' => $credencial['origen'] === self::ORIGEN_DESPACHO ? 'Despacho' : 'Sistema Adlatere',
            'Reason' => 'Firma de Actividad: ' . ($actividad->tipoActividad->nombre ?? 'Documento Legal'),
            'ContactInfo' => $credencial['email'] ?? $operador->email,
        ];

        $verificationPath = route('actividades.verificar', [
            'actividad' => $actividad->id,
            'hash' => $hashOriginal,
        ], false);
        $verificationUrl = rtrim(request()->getSchemeAndHttpHost(), '/') . $verificationPath;

        $qrY = $size['height'] - 58;
        $pdf->write2DBarcode($verificationUrl, 'QRCODE,H', 15, $qrY, 24, 24, [], 'N');
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(43, $qrY + 4);
        $pdf->Cell(0, 4, 'Validar en el sistema:', 0, 1, 'L');
        $pdf->SetX(43);
        $pdf->Cell(0, 4, $certs['titular'], 0, 1, 'L');
        $pdf->SetX(43);
        $pdf->Cell(0, 4, 'Fecha: ' . date('d/m/Y H:i'), 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);

        $pdf->setSignature($certs['cert'], $certs['pkey'], '', '', 2, $info);
        $pdf->Output($pdfFirmadoFull, 'F');

        return ($relativeDir !== '.' ? $relativeDir . '/' : '') . $pdfFirmadoName;
    }

    private function resolverCredencial(User $operador, string $origen, ?string $personalPath = null): array
    {
        if ($origen === self::ORIGEN_PERSONAL) {
            if (empty($personalPath) && empty($operador->firma_path)) {
                throw new \Exception('Seleccione su archivo de certificado personal.');
            }

            return [
                'origen' => self::ORIGEN_PERSONAL,
                'path' => $personalPath ?: Storage::disk('local')->path((string) $operador->firma_path),
                'email' => $operador->email,
            ];
        }

        $config = Setting::first();
        if (!$config) {
            throw new \Exception('No hay configuración de empresa para el certificado del despacho.');
        }

        $mismo = $config->firma_actividades_mismo_certificado === null
            ? true
            : (bool) $config->firma_actividades_mismo_certificado;
        $archivo = $mismo ? $config->cert_file : $config->firma_actividades_cert_file;

        if (!$archivo) {
            throw new \Exception('No hay un certificado del despacho configurado.');
        }

        return [
            'origen' => self::ORIGEN_DESPACHO,
            'path' => Storage::disk('certificados')->path($archivo),
            'email' => $config->email,
        ];
    }

    private function infoCertificadoPersonal(User $operador, bool $autorizado, ?string $personalPath = null): array
    {
        $info = [
            'disponible' => false,
            'permitido' => $autorizado,
            'titular' => null,
            'vencimiento' => null,
            'motivo' => null,
        ];

        if (!$autorizado) {
            $info['motivo'] = 'No tiene permiso para firmar esta actividad.';
            return $info;
        }

        if (empty($personalPath) && empty($operador->firma_path)) {
            $info['motivo'] = 'Seleccione su archivo de certificado personal.';
            return $info;
        }

        $path = $personalPath ?: Storage::disk('local')->path($operador->firma_path);
        if (!is_file($path)) {
            $info['motivo'] = 'El archivo de certificado personal no existe en disco.';
            return $info;
        }

        $info['disponible'] = true;

        if (empty($operador->firma_password)) {
            $info['motivo'] = 'Ingrese la contraseña del certificado al firmar.';
            return $info;
        }

        try {
            $certs = $this->cargarPkcs12($path, Crypt::decryptString($operador->firma_password));
            $info['titular'] = $certs['titular'];
            $info['vencimiento'] = $certs['valid_to'] ? date('d/m/Y', $certs['valid_to']) : null;
        } catch (\Throwable $e) {
            $info['motivo'] = 'Ingrese la contraseña del certificado al firmar.';
        }

        return $info;
    }

    private function infoCertificadoDespacho(?Setting $config, bool $autorizado): array
    {
        $info = [
            'disponible' => false,
            'permitido' => $autorizado,
            'titular' => null,
            'vencimiento' => null,
            'motivo' => null,
        ];

        if (!$autorizado) {
            $info['motivo'] = 'No tiene permiso para firmar con el certificado del despacho. Solicite al administrador que habilite la opción en su usuario.';
            return $info;
        }

        if (!$config) {
            $info['motivo'] = 'No hay configuración de empresa.';
            return $info;
        }

        $mismo = $config->firma_actividades_mismo_certificado === null
            ? true
            : (bool) $config->firma_actividades_mismo_certificado;
        $archivo = $mismo ? $config->cert_file : $config->firma_actividades_cert_file;
        $password = $mismo ? $config->cert_password : $config->firma_actividades_cert_password;

        if (!$archivo) {
            $info['motivo'] = 'No hay un certificado del despacho configurado.';
            return $info;
        }

        $path = Storage::disk('certificados')->path($archivo);
        if (!is_file($path)) {
            $info['motivo'] = 'El archivo del certificado del despacho no existe en disco.';
            return $info;
        }

        $info['disponible'] = true;

        if (!$password) {
            $info['motivo'] = 'Ingrese la contraseña del certificado al firmar.';
            return $info;
        }

        try {
            $certs = $this->cargarPkcs12($path, $password);
            $info['titular'] = $certs['titular'];
            $info['vencimiento'] = $certs['valid_to'] ? date('d/m/Y', $certs['valid_to']) : null;
        } catch (\Throwable $e) {
            $info['motivo'] = 'Ingrese la contraseña del certificado al firmar.';
        }

        return $info;
    }

    private function modoFirma(?Setting $config): string
    {
        $modo = $config->firma_actividades_modo ?? 'manual';
        return in_array($modo, ['manual', 'predeterminado', 'automatico'], true) ? $modo : 'manual';
    }

    private function esAdmin(User $user): bool
    {
        return ($user->profile ?? null) === 'Admin'
            || (method_exists($user, 'hasRole') && $user->hasRole('Admin'));
    }

    private function esPatrocinador(Actividad $actividad, User $user): bool
    {
        if (!$actividad->juicio) {
            return false;
        }

        foreach ($actividad->juicio->abogados as $abogado) {
            if ((int) $abogado->id !== (int) $user->id) {
                continue;
            }

            $rol = strtolower(trim((string) ($abogado->pivot->rol_en_juicio ?? '')));
            return str_contains($rol, 'abogado') && str_contains($rol, 'patrocinador');
        }

        return false;
    }
}
