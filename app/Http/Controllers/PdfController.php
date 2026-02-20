<?php

namespace App\Http\Controllers;

use App\Mail\FacturaMail;
use App\Models\Arqueo;
use App\Models\Factura;
use App\Models\Setting;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;

class PdfController extends Controller
{

    public function generatePdf(Factura $factura, $download = false){

        $empresa =  empresa();
        
        // Fallback robusto si no hay configuración cargada (evita Error 500)
        if (!$empresa) {
            $empresa = (object)[
                'razonSocial' => 'FACTA - SISTEMA DE FACTURACIÓN',
                'nombreComercial' => 'FACTA',
                'ruc' => '1790000000001',
                'dirMatriz' => 'Quito, Ecuador',
                'email' => 'soporte@facta.ec',
                'logo' => 'noimg',
                'estab' => '001',
                'ptoEmi' => '001',
                'ambiente' => 1
            ];
        }
        //$empresa =  Setting::first();
        // Limpia cualquier salida previa
        //dd('hola pdf ctm');
        if($download) {
             ob_end_clean();
             ob_start();
        }
        // foreach($factura->detalles as $detalle){
        //     dd($detalle);
        // };
        //dd($empresa->razonSocial, $factura->detalles);
        // Crear el PDF con FPDF
        $pdf = new Fpdf();
        $pdf->SetCreator($empresa->razonSocial);
		$pdf->SetAuthor($empresa->razonSocial);
		$pdf->SetTitle('factura');
		$pdf->SetSubject('PDF');
		$pdf->SetKeywords('FPDF, PDF, cheque, impresion, guia');
		$pdf->SetMargins('10', '10', '10');
		$pdf->SetAutoPageBreak(TRUE);
		$pdf->SetFont('Arial', '', 7);
		$pdf->AddPage();
		
        // LOGO
        $logoPath = $empresa->logo ? public_path($empresa->logo) : null;

        if($logoPath && $empresa->logo !== 'noimg' && file_exists($logoPath) && is_file($logoPath)){
             // Get dimensions with error suppression
             $dims = @getimagesize($logoPath);
             
             if ($dims) {
                 list($width_orig, $height_orig) = $dims;
                 $max_width = 35;
                 $max_height = 35;
                 
                 $ratio_orig = $width_orig / $height_orig;
                 
                 if ($max_width / $max_height > $ratio_orig) {
                    $width = $max_height * $ratio_orig;
                    $height = $max_height;
                 } else {
                    $height = $max_width / $ratio_orig;
                    $width = $max_width;
                 }
                 
                 try {
                    $pdf->Image($logoPath, 10, 15, $width, $height); 
                 } catch (\Exception $e) {
                     // If FPDF fails to parse image, just ignore logo
                     $pdf->SetFont('Arial', '', 10);
                     $pdf->SetXY(10, 25);
                     $pdf->Cell(40, 10, 'Error Logo', 1, 1, 'C');
                 }
             }
        } else {
             $pdf->SetFont('Arial', 'B', 12);
             $pdf->SetXY(10, 25);
             $pdf->Cell(40, 10, substr($empresa->nombreComercial ?? 'FACTA', 0, 15), 0, 1, 'C'); // Fallback text
        }

		$pdf->SetXY(107, 10);
		$pdf->Cell(93, 84, '', 1, 1);
		$pdf->SetXY(10, 54);
		$pdf->Cell(93, 40, '', 1, 1);
		$pdf->SetXY(10, 98);
		$pdf->Cell(190, 12, '', 1, 1);
		$pdf->SetXY(10, 114);
		$pdf->Cell(190, 173, '', 0, 1);
		$pdf->SetFont('Arial', 'B', 6);$pdf->SetXY(10, 54);$pdf->Cell(93, 10, utf8_decode($empresa->razonSocial ?? ''), 0 , 1, 'C');
		 $pdf->SetFont('Arial', '', 6);$pdf->SetXY(10, 59);$pdf->Cell(93, 10, ' MATRIZ', 0 , 1, 'L');
		$pdf->SetFont('Arial', 'B', 7);$pdf->SetXY(10, 68);$pdf->MultiCell(93, 10, utf8_decode($empresa->dirMatriz ?? ''), 0 , 'C');
		$pdf->SetFont('Arial', '', 6);$pdf->SetXY(25, 68);$pdf->MultiCell(78, 4, 'SUCURSAL', 0 , 'L');
		$pdf->SetFont('Arial', 'B', 7);$pdf->SetXY(10, 80);$pdf->MultiCell(15, 4, utf8_decode($empresa->disSucursal ?? ''), 0 , 'C');
		// $pdf->SetFont('Arial', '', 6);$pdf->SetXY(25, 80);$pdf->MultiCell(78, 4, 'VIA QUITO', 0 , 'L');
		$pdf->SetFont('Arial', 'B', 9);$pdf->SetXY(107, 10);$pdf->Cell(40, 8, 'RUC:'. ' '. $empresa->ruc, 0 , 1);
		
        $dy = 0; // Vertical offset
        if ($factura->codDoc == '04') {
            
            $pdf->SetFont('Arial', '', 9);$pdf->SetXY(107, 18);$pdf->Cell(93, 8, utf8_decode('NOTA DE CRÉDITO'), 0 , 1);
            $pdf->SetFont('Arial', '', 9);$pdf->SetXY(107, 24);$pdf->Cell(40, 8, 'No: '. $factura->secuencial, 0 , 1);
            
            // Show Modified Document Info
            $pdf->SetFont('Arial', 'B', 7);$pdf->SetXY(107, 30);$pdf->Cell(93, 4, 'DOC .MODIFICADO: FACTURA', 0 , 1, 'L');
            if($factura->facturaModificada) {
                 $serieMod = $empresa->estab . '-' . $empresa->ptoEmi . '-' . $factura->facturaModificada->secuencial;
                 $pdf->SetFont('Arial', '', 7);$pdf->SetXY(150, 30);$pdf->Cell(50, 4, $serieMod, 0 , 1, 'L');
                 $pdf->SetFont('Arial', 'B', 7);$pdf->SetXY(107, 34);$pdf->Cell(93, 4, 'FECHA EMISION DOC. SUSTENTO: ' . $factura->facturaModificada->created_at->format('d/m/Y'), 0 , 1, 'L');
            }
             $pdf->SetFont('Arial', 'B', 7);$pdf->SetXY(107, 38);$pdf->Cell(93, 4, 'RAZON MODIFICACION: ' . utf8_decode(strtoupper(substr($factura->motivo_nc ?? '', 0, 30))), 0 , 1, 'L');
             
             // Dynamic Offset for NC
             $dy = 10; 

        } else {
            $pdf->SetFont('Arial', '', 9);$pdf->SetXY(107, 18);$pdf->Cell(93, 8, 'FACTURA', 0 , 1);
            $pdf->SetFont('Arial', '', 9);$pdf->SetXY(107, 26);$pdf->Cell(40, 8, 'No: '. $factura->secuencial, 0 , 1);
        }

		$pdf->SetFont('Arial', '', 9);$pdf->SetXY(107, 32 + $dy);$pdf->Cell(40, 10, 'FECHA AUTORIZACION:' . '   '. $factura->fechaAutorizacion, 0 , 1);
		
		$pdf->SetFont('Arial', 'B', 7);$pdf->SetXY(107, 42 + $dy);$pdf->Cell(93, 8, 'NUMERO DE AUTORIZACION', 0 , 1, 'C');
		$pdf->SetFont('Arial', '', 7);$pdf->SetXY(107, 50 + $dy);$pdf->Cell(93, 10, $factura->numeroAutorizacion ?? 'PENDIENTE DE AUTORIZACION', 0 , 1, 'C');
		$pdf->SetFont('Arial', 'B', 7);$pdf->SetXY(107, 66 + $dy);$pdf->Cell(93, 4, 'CLAVE DE ACCESO', 0 , 1, 'C');

        if ($factura->numeroAutorizacion) {
            $barcodeGenerator = new BarcodeGeneratorPNG();
            try {
                // Generar el código de barras
                $barcodeData = $barcodeGenerator->getBarcode(
                    (string) $factura->numeroAutorizacion,
                    BarcodeGeneratorPNG::TYPE_CODE_128
                );
                // Guardar el código de barras como un archivo temporal único en storage
                $barcodeFile = storage_path('app/public/temp_barcode_' . $factura->id . '_' . time() . '.png');
                file_put_contents($barcodeFile, $barcodeData);
                
                // Insertar el código de barras
                if (file_exists($barcodeFile)) {
                    $pdf->Image($barcodeFile, 108, 70 + $dy, 90, 10); 
                    unlink($barcodeFile); // Limpiar archivo temporal
                }
            } catch (\Exception $e) {
                \Log::error('Error generando código de barras PDF: ' . $e->getMessage());
                // Si falla el código de barras, no rompemos el PDF
            }
        }
		$pdf->SetFont('Arial', 'B', 7);
		$pdf->SetXY(107, 80 + $dy);
		$pdf->Cell(93, 5, $factura->numeroAutorizacion ?? $factura->claveAcceso, 0 , 1, 'C');

		$pdf->SetFont('Arial', 'B', 6);$pdf->SetXY(10, 98);$pdf->Cell(30, 3, 'RAZON SOCIAL', 0 , 1, 'C');
		$pdf->SetXY(10, 101);$pdf->Cell(30, 3, 'NOMBRES Y APELLIDOS', 0 , 0, 'C');
		$pdf->SetFont('Arial', '', 7);$pdf->SetXY(40, 98);$pdf->MultiCell(160, 3, $factura->customer->businame,0,'L');
		$pdf->SetFont('Arial', 'B', 6);$pdf->SetXY(10, 104);$pdf->Cell(30, 6, 'FECHA DE EMISION', 0 , 1, 'C');
		$pdf->SetFont('Arial', '', 7);$pdf->SetXY(40, 104);$pdf->Cell(100, 6, $factura->fechaAutorizacion, 0 , 1);
		$pdf->SetFont('Arial', 'B', 7);$pdf->SetXY(140, 104);$pdf->Cell(30, 6, 'IDENTIFICACION', 0 , 1);
		$pdf->SetFont('Arial', '', 7);$pdf->SetXY(170, 104);$pdf->Cell(30, 6, $factura->customer->valueidenti, 0 , 1);
		$pdf->SetFont('Arial', 'B', 7);

		$pdf->SetXY(10, 114);$pdf->Cell(13, 6, false, 1 , 1);
		$pdf->SetXY(10, 114);$pdf->Cell(13, 3, 'Cod.', 0 , 1, 'C');
		$pdf->SetXY(10, 117);$pdf->Cell(13, 3, 'Principal', 0 , 1, 'C');
		$pdf->SetXY(23, 114);$pdf->Cell(13, 6, false, 1 , 1);
		$pdf->SetXY(23, 114);$pdf->Cell(13, 3, 'Cod.', 0 , 1, 'C');
		$pdf->SetXY(23, 117);$pdf->Cell(13, 3, 'Auxiliar', 0 , 1, 'C');
		$pdf->SetXY(36, 114);$pdf->Cell(13, 6, 'Cant', 1 , 1, 'C');
		$pdf->SetXY(49, 114);$pdf->Cell(110, 6, 'DESCRIPCION', 1 , 1, 'C');
		$pdf->SetXY(159, 114);$pdf->Cell(13, 6, false, 1 , 1);
		$pdf->SetXY(159, 114);$pdf->Cell(13, 3, 'Precio', 0 , 1, 'C');
		$pdf->SetXY(159, 117);$pdf->Cell(13, 3, 'Unitario', 0 , 1, 'C');
		$pdf->SetXY(172, 114);$pdf->Cell(15, 6, 'Descuento', 1 , 1, 'C');
		$pdf->SetXY(187, 114);$pdf->Cell(13, 6, false, 1 , 1);
		$pdf->SetXY(187, 114);$pdf->Cell(13, 3, 'Precio', 0 , 1, 'C');
		$pdf->SetXY(187, 117);$pdf->Cell(13, 3, 'Total', 0 , 1, 'C');
		//CABECERA KARDEX TOTALES

		$ejey = 120;
        foreach($factura->detalles as $detalle){
            //dd($detalle);
		$pdf->SetXY(10, $ejey);$pdf->Cell(13, 10, $detalle->product_id, 1 , 1, 'C');  // codigo producto
		$pdf->SetXY(23, $ejey);$pdf->Cell(13, 10, '', 1 , 1, 'C');
		$pdf->SetXY(36, $ejey);$pdf->Cell(13, 10, $detalle->cantidad, 1 , 1, 'C');$pdf->SetFont('Arial', 'B', 5);  //cantidad
		$pdf->SetXY(49, $ejey);$pdf->Cell(110, 10, '', 1 , 0);
		$pdf->SetXY(49, $ejey);$pdf->MultiCell(110, 5,$detalle->descripcion,'L');$pdf->SetFont('Arial', 'B', 7);  //pridcuto
		$pdf->SetXY(159, $ejey);$pdf->Cell(13, 10, $detalle->precioUnitario, 1 , 1, 'C');  //precio unitario
        $descuento = ($detalle->descuento * $detalle->precioUnitario / 100) * $detalle->cantidad;
		$pdf->SetXY(172, $ejey);$pdf->Cell(15, 10, number_format($descuento,2), 1 , 1, 'C');  //descueto
        // Total (precio total menos el descuento aplicado)
        $total = ($detalle->precioUnitario * $detalle->cantidad) - $descuento;
		$pdf->SetXY(187, $ejey);$pdf->Cell(13, 10, number_format($total,2), 1 , 1, 'C');  //total

		$ejey += 10;
		//$ejey += 4;
    }
        //KARDEX TOTALES
		// $pdf->SetFont('Arial', 'B', 7);
		// $pdf->SetXY(120, $ejey);$pdf->Cell(50, 4, 'SUBTOTAL', 1 , 1, 'L');
		// $pdf->SetXY(120, $ejey+4);$pdf->Cell(50, 4, 'IVA 0%', 1 , 1, 'L');
		// $pdf->SetXY(120, $ejey+8);$pdf->Cell(50, 4, 'IVA 12%', 1 , 1, 'L');
		// $pdf->SetXY(120, $ejey+12);$pdf->Cell(50, 4, 'DESCUENTO $', 1 , 1, 'L');
		// $pdf->SetXY(120, $ejey+16);$pdf->Cell(50, 4, 'VALOR TOTAL', 1 , 1, 'L');
		// $pdf->SetXY(170, $ejey);$pdf->Cell(30, 4, $factura->total, 1 , 1, 'R');//SUBTOTAL
		// $pdf->SetXY(170, $ejey+4);$pdf->Cell(30, 4, $factura->subtotal0, 1 , 1, 'R');//IVA 0
		// $pdf->SetXY(170, $ejey+8);$pdf->Cell(30, 4, $factura->subtotal12, 1 , 1, 'R');//VALOR IVA
		// $pdf->SetXY(170, $ejey+12);$pdf->Cell(30, 4, $factura->descuento, 1 , 1, 'R');//VALOR DESCUENTO
		// $pdf->SetXY(170, $ejey+16);$pdf->Cell(30, 4, $factura->total, 1 , 1, 'R');//VALOR CON IVA

        // seccion totales e impuestos
        $impuestosAgrupados =  $factura->impuestos->groupBy('nombre_impuesto')
            ->map(function ($items){
                return $items->sum('valor_impuesto');
            });
            $pdf->SetFont('Arial', 'B', 7);
            $linea = 0;
            $yBase = $ejey;
            // SUBTOTAL
            $pdf->SetXY(120, $yBase + $linea);
            $pdf->Cell(50, 4, 'SUBTOTAL', 1, 1, 'L');
            $pdf->SetXY(170, $yBase + $linea);
            $pdf->Cell(30, 4, number_format($factura->subtotal, 2), 1, 1, 'R');
            $linea += 4;

            // IMPUESTOS AGRUPADOS
        foreach ($impuestosAgrupados as $nombre => $valor) {
            $pdf->SetXY(120, $yBase + $linea);
            $pdf->Cell(50, 4, $nombre, 1, 1, 'L');

            $pdf->SetXY(170, $yBase + $linea);
            $pdf->Cell(30, 4, number_format($valor, 2), 1, 1, 'R');

            $linea += 4;
        }

        // DESCUENTO
            $pdf->SetXY(120, $yBase + $linea);
            $pdf->Cell(50, 4, 'DESCUENTO $', 1, 1, 'L');
            $pdf->SetXY(170, $yBase + $linea);
            $pdf->Cell(30, 4, number_format($factura->descuento, 2), 1, 1, 'R');
            $linea += 4;

                    // TOTAL
            $pdf->SetXY(120, $yBase + $linea);
            $pdf->Cell(50, 4, 'VALOR TOTAL', 1, 1, 'L');
            $pdf->SetXY(170, $yBase + $linea);
            $pdf->Cell(30, 4, number_format($factura->total, 2), 1, 1, 'R');





		//INFO ADICIONAL
		$pdf->SetFont('Arial', 'B', 8);
		$pdf->SetXY(10, $ejey);$pdf->Cell(105, 6, 'INFORMACION ADICIONAL', 1 , 1, 'C');
		$pdf->SetFont('Arial', '', 7);
		$pdf->SetXY(10, $ejey+6);$pdf->Cell(20, 6, 'Email empresa:', 'L' , 1, 'L');
		$pdf->SetXY(10, $ejey+12);$pdf->Cell(20, 6, 'Email cliente:', 'L' , 1, 'L');
		$pdf->SetXY(10, $ejey+18);$pdf->Cell(20, 6, 'Telefono cliente:', 'L' , 1, 'L');
		$pdf->SetXY(30, $ejey+6);$pdf->Cell(85, 6, utf8_decode($empresa->email), 'R' , 1, 'L'); //email empresa
		$pdf->SetXY(30, $ejey+12);$pdf->Cell(85, 6, utf8_decode($factura->customer->email), 'R' , 1, 'L');  // email cliente
		$pdf->SetXY(30, $ejey+18);$pdf->Cell(85, 6, utf8_decode($factura->customer->phone), 'R' , 1, 'L');  //telefoo cliente
		$pdf->SetXY(10, $ejey+24);$pdf->MultiCell(105, 10, utf8_decode($factura->customer->address), 'LRB', 'L'); //direccio  cliente
		//FORMA DE PAGO


		$pdf->SetFont('Arial', 'B', 7);$pdf->SetXY(10, $ejey+39);$pdf->Cell(75, 6, 'Forma de pago', 1 , 1, 'C');
		$pdf->SetFont('Arial', 'B', 7);$pdf->SetXY(85, $ejey+39);$pdf->Cell(30, 6, 'Valor', 1 , 1, 'C');

        // Obtener descripción de la forma de pago
        $pMethod = PaymentMethod::where('code', $factura->formaPago)->first();
        $descripcionPago = $pMethod ? $pMethod->description : 'SIN UTILIZACION DEL SISTEMA FINANCIERO';

		$pdf->SetFont('Arial', '', 7);$pdf->SetXY(10, $ejey+45);$pdf->Cell(75, 6, substr(utf8_decode($descripcionPago), 0, 45), 'LRB' , 1, 'L');
		$pdf->SetFont('Arial', '', 7);$pdf->SetXY(85, $ejey+45);$pdf->Cell(30, 6, $factura->total, 'RB' , 1, 'L');

        // LEYENDA DEL NEGOCIO (Configurable en Settings)
        if (!empty($empresa->leyend)) {
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->SetXY(10, $ejey + 52); // Justo debajo de la forma de pago
            $pdf->MultiCell(105, 4, utf8_decode($empresa->leyend), 0, 'L');
        }

        // FOOTER DE MARCA (Desarrollado por Khipu)
        $brandingText = "Desarrollado por khipu. Contactos al 0987308688 - www.facta.ec";
        $pdf->SetY(-15); // Posicionarse 1.5 cm del fondo
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetTextColor(128, 128, 128); // Gris
        $pdf->Cell(0, 10, utf8_decode($brandingText), 0, 0, 'C');
        $pdf->SetTextColor(0, 0, 0); // Reset color

        // Salida del PDF
        $pdfContent = $pdf->Output('S');
        $fileName = $factura->customer->businame .'_'.$factura->secuencial;
        
        if($factura->codDoc == '04') {
            $fileName .= '_NC';
        }
        $fileName .= '.pdf';
        
        // Guardar en disco (opcional, ya estaba ahi)
        // Partitioning: YYYY/MM
        $relativePath = $factura->created_at->format('Y') . '/' . $factura->created_at->format('m') . '/';
        $fileNameWithDir = $relativePath . $fileName;
        
        Storage::disk('comprobantes/pdfs')->put($fileNameWithDir, $pdfContent);
        
        // $this->enviarFacturea($factura); // REMOVED TO PREVENT DUPLICATE EMAIL

        if($download) {
            return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $factura->customer->businame . '.pdf"');
        }

        return $pdfContent;

    }

    public  function pdfDowloader (Factura $factura){
        return $this->generatePdf($factura, true);
    }


    public function enviarFacturea(Factura $factura)  {
        $start = microtime(true);
        Log::info("[DEBUG_MAIL] Iniciando enviarFacturea para factura: " . $factura->secuencial);

        // 1. Garantizar que el PDF existe
        $pdf_name = $factura->customer->businame . '_' . $factura->secuencial;
        if ($factura->codDoc == '04') $pdf_name .= '_NC';
        
        $pdfRelative = $factura->created_at->format('Y') . '/' . $factura->created_at->format('m') . '/' . $pdf_name . '.pdf';
        $pdfPath = Storage::disk('comprobantes/pdfs')->path($pdfRelative);

        if (!file_exists($pdfPath)) {
            Log::info("[DEBUG_MAIL] PDF no encontrado, generando ahora.");
            $this->generatePdf($factura, false);
        }

        // 2. Despachar el Job (Usamos sync para detectar errores de configuración inmediatamente en logs si falla)
        try {
            Log::info("[DEBUG_MAIL] Despachando SendInvoiceEmail (Sync) para: " . $factura->secuencial);
            \App\Jobs\SendInvoiceEmail::dispatchSync($factura);
            Log::info("[DEBUG_MAIL] Proceso finalizado exitosamente.");
        } catch (\Exception $e) {
            Log::error('[DEBUG_MAIL] Error crítico enviando correo: ' . $e->getMessage());
            // Si falla el Job, lanzamos excepción para que el usuario reciba el feedback en la UI
            throw $e;
        }
    }


    public function arqueoDowloader (Arqueo $arqueo){

        //dd($arqueo);
        $pdf = new Fpdf();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Reporte de Arqueo de Caja', 0, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(5);

    $pdf->Cell(50, 10, 'ID Arqueo:', 0, 0);
    $pdf->Cell(50, 10, $arqueo->id, 0, 1);
    $pdf->Cell(50, 10, 'Usuario:', 0, 0);
    $pdf->Cell(50, 10, $arqueo->user->name ?? '---', 0, 1);
    $pdf->Cell(50, 10, 'Caja:', 0, 0);
    $pdf->Cell(50, 10, $arqueo->caja->nombre ?? '---', 0, 1);
    $pdf->Cell(50, 10, 'Fecha apertura:', 0, 0);
    $pdf->Cell(50, 10, $arqueo->fecha_apertura, 0, 1);
    $pdf->Cell(50, 10, 'Fecha cierre:', 0, 0);
    $pdf->Cell(50, 10, $arqueo->fecha_cierre ?? '---', 0, 1);
    $pdf->Cell(50, 10, 'Total en caja:', 0, 0);
    $pdf->Cell(50, 10, '$' . number_format($arqueo->total, 2), 0, 1);

    // Obtener el contenido del PDF
    $pdfContent = $pdf->Output('S');

    // Forzar descarga
    return response($pdfContent)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="arqueo_' . $arqueo->id . '.pdf"')
        ->header('Content-Length', strlen($pdfContent));
    }
}
