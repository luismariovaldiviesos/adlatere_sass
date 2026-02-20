<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class FacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $factura;
    public $pdfPath;
    public $xmlPath;

    public function __construct($factura, $pdfPath, $xmlPath)
    {
        $this->factura = $factura;
        $this->pdfPath = $pdfPath;
        $this->xmlPath = $xmlPath;
    }


    public function build()
    {
        $tipoDoc = 'Factura';
        $body = "Estimado cliente, adjuntamos su factura electrónica. Gracias por su preferencia.";
        
        if ($this->factura->codDoc == '04') {
             $tipoDoc = 'Nota de Crédito';
             $body = "Estimado cliente, adjuntamos su Nota de Crédito electrónica. Gracias por su preferencia.";
        }

        return $this
            ->subject("$tipoDoc N° {$this->factura->secuencial} - " . config('mail.from.name'))
            ->html($body)
            ->attach($this->pdfPath, [
                'as' => "{$this->factura->secuencial}.pdf",
                'mime' => 'application/pdf',
            ])
            ->attach($this->xmlPath, [
                'as' => "{$this->factura->secuencial}.xml",
                'mime' => 'application/xml',
            ]);
    }
}
