<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\User;
use App\Models\Documento;
use App\Models\JuicioHistorialEstado;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
//use setasign\Fpdi\Fpdi;
use setasign\FpdfSignature\FpdfSignature;
use setasign\Fpdi\Tcpdf\Fpdi;


class ActividadSignatureService {

        public function firmar(\App\Models\Actividad $actividad, \App\Models\User $abogado){

        $this->validarPrecondiciones($actividad, $abogado);
        $pdfOriginalPath = $this->generarPdfOriginal($actividad);
        $certs = $this->cargarPkcs12($abogado);
        $this->validarVigencia($certs['cert']);
        
        $pdfFirmadoPath = $this->firmarPdfConFirmaVisible($pdfOriginalPath, $certs, $abogado, $actividad);
        
        // HASH LEYENDO EL PDF DESDE EL DISCO PUBLIC
        $hashOriginal = hash_file('sha256', \Illuminate\Support\Facades\Storage::disk('public')->path('actividades/originales/' . $pdfOriginalPath));
        
        $actividad->update([
            'estado_firma'        => 'firmada',
            'firmado_por_user_id' => $abogado->id,
            'firmado_en'          => now(),
            'pdf_original_path'   => $pdfOriginalPath,
            'pdf_firmado_path'    => $pdfFirmadoPath,
            'firma_hash'          => $hashOriginal,
            'firma_metadatos'     => json_encode([
                'cert_serial'     => $certs['serial'],
                'cert_issuer'     => $certs['issuer'],
                'cert_subject'    => $certs['subject'],
                'algorithm'       => 'sha256WithRSAEncryption',
                'standard'        => 'PAdES-BES',
            ]),
        ]);

        $doc = \App\Models\Documento::create([
            'juicio_id'      => $actividad->juicio_id,
            'origen_tipo'    => 'Actividad',
            'origen_id'      => $actividad->id,
            'nombre'         => 'Actividad Firmada Electrónicamente - ' . ($actividad->tipoActividad->nombre ?? ''),
            // RUTA COMPLETA PARA QUE EL ENLACE DE DESCARGA FUNCIONE EN TODAS PARTES
            'ruta_archivo'   => 'actividades/firmados/' . $pdfFirmadoPath,
            'tipo_archivo'   => 'pdf',
            // TAMAÑO LEYENDO EL PDF DESDE EL DISCO PUBLIC
            'tamaño_archivo' => round(\Illuminate\Support\Facades\Storage::disk('public')->size('actividades/firmados/' . $pdfFirmadoPath) / 1024, 2),
        ]);

        \App\Models\JuicioHistorialEstado::create([
            'juicio_id'          => $actividad->juicio_id,
            'user_id'            => $abogado->id,
            'estado_procesal_id' => $actividad->juicio->estado_procesal_id ?? null,
            'tipo_movimiento'    => 'actividad_firmada',
            'referencia_tipo'    => 'Actividad',
            'referencia_id'      => $actividad->id,
            'descripcion'        => "Actividad '{$actividad->tipoActividad->nombre}' firmada digitalmente por {$abogado->name}",
        ]);

        return ['documento_id' => $doc->id, 'pdf_firmado_path' => $pdfFirmadoPath, 'hash' => $hashOriginal];
    }

    public function validarPrecondiciones(Actividad $actividad, User $abogado): void {
        if (!$actividad->tipoActividad->es_firmable) {
            throw new \Exception('Este tipo de actividad no permite firma electrónica.');
        }
        if ($actividad->estado_firma === 'firmada') {
            throw new \Exception('La actividad ya está firmada.');
        }
        if (!$actividad->juicio->abogados()->where('user_id', $abogado->id)->whereRaw("TRIM(LOWER(rol_en_juicio)) LIKE ?", ['%abogado%patrocinador%'])->exists()) {
            throw new \Exception('Solo el abogado patrocinador del juicio puede firmar esta actividad.');
        }
        $esAbogado = (isset($abogado->profile) && $abogado->profile === 'Abogado')
            || (method_exists($abogado, 'hasRole') && $abogado->hasRole('Abogado'));
        if (!$esAbogado) {
            throw new \Exception('Solo un usuario con perfil de abogado puede firmar.');
        }
        if (empty($abogado->firma_path) || empty($abogado->firma_password)) {
            throw new \Exception('No tienes configurada tu firma electrónica en tu perfil.');
        }
        if (!Storage::disk('local')->exists($abogado->firma_path)) {
            throw new \Exception('El archivo de certificado no existe en disco. Sube uno nuevo en tu perfil.');
        }
    }

        private function generarPdfOriginal(\App\Models\Actividad $actividad): string
    {
        $actividad->loadMissing(['juicio.actores', 'juicio.demandados', 'tipoActividad', 'user']);

        $html = view('pdf.actividad', ['actividad' => $actividad])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'defaultFont'     => 'DejaVu Sans',
                'fontDir'         => storage_path('fonts'),
                'fontCache'       => storage_path('fonts'),
            ]);

        $relativePath = $actividad->created_at->format('Y/m/') . "actividad_{$actividad->id}.pdf";
        
        // GUARDANDO EL PDF EN EL DISCO PUBLIC (SaaS Aislado)
        \Illuminate\Support\Facades\Storage::disk('public')->put('actividades/originales/' . $relativePath, $pdf->output());

        return $relativePath;
    }

        private function cargarPkcs12(User $abogado): array
    {
        $path = Storage::disk('local')->path($abogado->firma_path);
        $password = Crypt::decryptString($abogado->firma_password);

        $pkcs12 = file_get_contents($path);
        $certs = [];
        if (!openssl_pkcs12_read($pkcs12, $certs, $password)) {
            throw new \Exception('Error leyendo PKCS#12: contraseña incorrecta o archivo corrupto.');
        }
        $certData = openssl_x509_parse($certs['cert']);
        return [
            'cert'       => $certs['cert'],
            'pkey'       => $certs['pkey'],
            'extracerts' => $certs['extracerts'] ?? null,
            'serial'     => $certData['serialNumber'] ?? '',
            'issuer'     => $certData['issuer']['CN'] ?? '',
            'subject'    => $certData['subject']['CN'] ?? '',
        ];
    }

     private function validarVigencia($cert): void
    {
        $data = openssl_x509_parse($cert);
        if (($data['validTo_time_t'] ?? 0) < time()) {
            throw new \Exception('El certificado digital ha expirado.');
        }
    }


        private function firmarPdfConFirmaVisible(string $originalPath, array $certs, \App\Models\User $abogado, \App\Models\Actividad $actividad): string
    {
        // RUTAS DE PDFs APUNTANDO AL DISCO PUBLIC
        $originalFull = \Illuminate\Support\Facades\Storage::disk('public')->path('actividades/originales/' . $originalPath);
        
        $baseName = basename($originalPath, '.pdf');
        $pdfFirmadoName = $baseName . '_firmado_' . time() . '.pdf';
        
        $relativeDir = dirname($originalPath);
        $outputDir = 'actividades/firmados' . ($relativeDir !== '.' ? '/' . $relativeDir : '');
        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($outputDir);
        
        $pdfFirmadoFull = \Illuminate\Support\Facades\Storage::disk('public')->path($outputDir . '/' . $pdfFirmadoName);

        // Inicializar FPDI para clonar el PDF exacto de DomPDF
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pageCount = $pdf->setSourceFile($originalFull);
        
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }

        // EL CERTIFICADO P12 SIGUE APUNTANDO AL DISCO LOCAL (POR ESTRICTA SEGURIDAD)
        $firmaPath = \Illuminate\Support\Facades\Storage::disk('local')->path($abogado->firma_path);
        $password = \Illuminate\Support\Facades\Crypt::decryptString($abogado->firma_password);

        // Configurar Metadatos de la Firma
        $info = array(
            'Name' => $abogado->name,
            'Location' => 'Sistema Adlatere SaaS',
            'Reason' => 'Firma de Actividad: ' . ($actividad->tipoActividad->nombre ?? 'Documento Legal'),
            'ContactInfo' => $abogado->email,
        );

        // SELLAR EL PDF USANDO LAS LLAVES PEM EXTRAÍDAS
        $pdf->setSignature($certs['cert'], $certs['pkey'], '', '', 2, $info);

        // Dibujar un cuadro visual de firma en la última página
        $pdf->SetFont('helvetica', '', 9);
        $textoFirma = "FIRMADO DIGITALMENTE POR:\n" . strtoupper($abogado->name) . "\nFECHA: " . date('Y-m-d H:i:s');
        
        $pdf->setSignatureAppearance(15, $size['height'] - 30, 80, 20); 
        $pdf->MultiCell(80, 20, $textoFirma, 1, 'C', false, 0, 15, $size['height'] - 30);

        // Generar el archivo sellado en el disco público
        $pdf->Output($pdfFirmadoFull, 'F');

        return ($relativeDir !== '.' ? $relativeDir . '/' : '') . $pdfFirmadoName;
    }






}