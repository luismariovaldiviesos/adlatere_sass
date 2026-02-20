<?php

namespace App\Jobs;

use App\Mail\FacturaMail;
use App\Models\Factura;
use App\Models\Setting;
use App\Models\MailConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendInvoiceEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $factura;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Factura $factura)
    {
        $this->factura = $factura;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $start = microtime(true);
        Log::info("[DEBUG_JOB] Iniciando envío de correo para factura: " . $this->factura->secuencial);
        
        try {
            $settings = Setting::first();
            
            // 1. Reconstruir nombres base
            $pdf_name = $this->factura->customer->businame . '_' . $this->factura->secuencial;
            $xml_name = $this->factura->customer->valueidenti . '_' . $this->factura->secuencial;
            
            if ($this->factura->codDoc == '04') {
                $pdf_name .= '_NC';
                $xml_name .= '_NC';
            }

            $datePath = $this->factura->created_at->format('Y/m/');
            
            // 2. Localizar PDF
            $pdfRelative = $datePath . $pdf_name . '.pdf';
            $pdfPath = \Illuminate\Support\Facades\Storage::disk('comprobantes/pdfs')->path($pdfRelative);
            
            if (!file_exists($pdfPath)) {
                // Fallback a raíz
                $pdfPath = \Illuminate\Support\Facades\Storage::disk('comprobantes/pdfs')->path($pdf_name . '.pdf');
            }

            // 3. Localizar XML (Búsqueda exhaustiva)
            $xml_file = $xml_name . '.xml';
            $xmlPath = null;
            $disks = ['comprobantes/autorizados', 'comprobantes/firmados', 'comprobantes/enviados'];
            
            foreach ($disks as $disk) {
                // Probar con fecha
                $tempPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($datePath . $xml_file);
                if (file_exists($tempPath)) {
                    $xmlPath = $tempPath;
                    break;
                }
                // Probar en raíz del disco
                $tempPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($xml_file);
                if (file_exists($tempPath)) {
                    $xmlPath = $tempPath;
                    break;
                }
            }

            // 4. Validar y Enviar
            if (file_exists($pdfPath) && $xmlPath && file_exists($xmlPath)) {
                Log::info("[DEBUG_JOB] Archivos localizados. PDF: " . basename($pdfPath) . " | XML: " . basename($xmlPath));
                
                $sendgridKey = config('services.sendgrid.key');

                if (!empty($sendgridKey)) {
                    $this->sendViaSendGridApi($sendgridKey, $pdfPath, $xmlPath, $settings);
                    Log::info("[DEBUG_JOB] Envío exitoso vía SendGrid API.");
                    return;
                } else {
                    Log::error("[DEBUG_JOB] Error: SENDGRID_API_KEY no está configurado en el servidor (.env).");
                    throw new \Exception("Configuración de correo incompleta (API Key faltante).");
                }
            } else {
                $errorMsg = "Archivos insuficientes para enviar correo. ";
                if (!file_exists($pdfPath)) $errorMsg .= "Falta PDF ($pdfPath). ";
                if (!$xmlPath || !file_exists($xmlPath)) $errorMsg .= "Falta XML. ";
                Log::error("[DEBUG_JOB] " . $errorMsg);
                throw new \Exception($errorMsg);
            }

        } catch (\Exception $e) {
            Log::error("[DEBUG_JOB] Excepción en Job: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Envía el correo usando la API de SendGrid (Puerto 443) para saltar bloqueos de red.
     */
    private function sendViaSendGridApi($apiKey, $pdfPath, $xmlPath, $settings)
    {
        $client = new \GuzzleHttp\Client();
        
        $fromEmail = env('MAIL_FROM_ADDRESS', 'noreply@facta.ec');
        $fromName = $settings->razonSocial ?? 'Sistema de Facturación';
        $replyTo = $settings->email ?? $fromEmail; // El correo del tenant registrado en "Mi Empresa"

        Log::info("[DEBUG_JOB] Usando Remitente (From): " . $fromEmail);

        $subject = ($this->factura->codDoc == '04' ? 'Nota de Crédito: ' : 'Factura: ') . $this->factura->secuencial;

        $payload = [
            'personalizations' => [
                [
                    'to' => [['email' => $this->factura->customer->email]],
                    'subject' => $subject
                ]
            ],
            'from' => ['email' => $fromEmail, 'name' => $fromName],
            'reply_to' => ['email' => $replyTo, 'name' => $fromName],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => "Estimado(a) <b>" . $this->factura->customer->businame . "</b>,<br><br>Adjuntamos su comprobante electrónico <b>" . $this->factura->secuencial . "</b>.<br><br>Saludos,<br>" . $fromName
                ]
            ],
            'attachments' => [
                [
                    'content' => base64_encode(file_get_contents($pdfPath)),
                    'type' => 'application/pdf',
                    'filename' => basename($pdfPath),
                    'disposition' => 'attachment'
                ],
                [
                    'content' => base64_encode(file_get_contents($xmlPath)),
                    'type' => 'text/xml',
                    'filename' => basename($xmlPath),
                    'disposition' => 'attachment'
                ]
            ]
        ];

        Log::info("[DEBUG_JOB] Enviando payload a SendGrid. Destino: " . $this->factura->customer->email);
        
        try {
            $response = $client->post('https://api.sendgrid.com/v3/mail/send', [
                'headers' => [
                    'Authorization' => "Bearer " . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            if ($response->getStatusCode() != 202) {
                $errorBody = (string) $response->getBody();
                Log::error("[DEBUG_JOB] Error en SendGrid API. Status: " . $response->getStatusCode() . " Body: " . $errorBody);
                throw new \Exception("Error en SendGrid API: Status " . $response->getStatusCode() . " - " . $errorBody);
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = (string) $e->getResponse()->getBody();
            Log::error("[DEBUG_JOB] Excepción Cliente Guzzle: " . $e->getMessage() . " Response: " . $errorBody);
            throw new \Exception("Error de Cliente SendGrid: " . $errorBody);
        } catch (\Exception $e) {
            Log::error("[DEBUG_JOB] Error inesperado en Guzzle: " . $e->getMessage());
            throw $e;
        }
    }
}
