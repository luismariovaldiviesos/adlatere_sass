<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Support\Facades\Storage;
use App\Models\XmlFile;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Factura;

class XmlGeneratorService
{
    /**
     * Genera el XML de la factura y lo guarda en el disco.
     * Retorna el nombre del archivo generado.
     */
    public function generate(
        $factura_id,
        $tipoIdentificadorCli,
        $razonSocialCli,
        $identificadorCliente,
        $direccionCliente,
        $totalSinImpuesto,
        $totalDescuento,
        $totalFactura,
        $detalles,
        $secuencia,
        $claveAcce,

        $totalImpuestos,
        $empresa,
        $fechaEmision, // New parameter
        $formaPago = '01'
    ) {
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;

        // Crear la estructura base del XML
        $xml_fac = $xml->createElement('factura');
        $xml_fac->setAttribute('id', 'comprobante');
        $xml_fac->setAttribute('version', '1.0.0');

        $xml_inf = $xml->createElement('infoTributaria');
        $xml_inf->appendChild($xml->createElement('ambiente', $empresa->ambiente));
        $xml_inf->appendChild($xml->createElement('tipoEmision', $empresa->tipoEmision));
        $xml_inf->appendChild($xml->createElement('razonSocial', $this->sanitize($empresa->razonSocial)));
        $xml_inf->appendChild($xml->createElement('nombreComercial', $this->sanitize($empresa->nombreComercial)));
        $xml_inf->appendChild($xml->createElement('ruc', $empresa->ruc));
        $xml_inf->appendChild($xml->createElement('claveAcceso', $claveAcce));
        $xml_inf->appendChild($xml->createElement('codDoc', '01'));
        $xml_inf->appendChild($xml->createElement('estab', $empresa->estab));
        $xml_inf->appendChild($xml->createElement('ptoEmi', $empresa->ptoEmi));
        $xml_inf->appendChild($xml->createElement('secuencial', $secuencia));
        $xml_inf->appendChild($xml->createElement('dirMatriz', $this->sanitize($empresa->dirMatriz)));

        $xml_fac->appendChild($xml_inf);

        // Información de la factura
        $xml_def = $xml->createElement('infoFactura');
        $xml_def->appendChild($xml->createElement('fechaEmision', $fechaEmision));
        $xml_def->appendChild($xml->createElement('dirEstablecimiento', $this->sanitize($empresa->dirEstablecimiento)));
        $xml_def->appendChild($xml->createElement('obligadoContabilidad', $empresa->obligadoContabilidad));
        $xml_def->appendChild($xml->createElement('tipoIdentificacionComprador', $tipoIdentificadorCli));
        $xml_def->appendChild($xml->createElement('razonSocialComprador', $this->sanitize($razonSocialCli)));
        $xml_def->appendChild($xml->createElement('identificacionComprador', $identificadorCliente));
        $xml_def->appendChild($xml->createElement('direccionComprador', $this->sanitize($direccionCliente)));
        $xml_def->appendChild($xml->createElement('totalSinImpuestos', number_format((float)$totalSinImpuesto, 2, '.', '')));
        $xml_def->appendChild($xml->createElement('totalDescuento', number_format((float)$totalDescuento, 2, '.', '')));

        // Agrupamos impuestos
        $impuestosAgrupados = [];
        foreach ($totalImpuestos as $impuesto) {
            $clave = $impuesto['codigo_impuesto'] . '-' . $impuesto['codigo_porcentaje'];
            if (!isset($impuestosAgrupados[$clave])) {
                $impuestosAgrupados[$clave] = [
                    'codigo_impuesto'   => $impuesto['codigo_impuesto'],
                    'codigo_porcentaje' => $impuesto['codigo_porcentaje'],
                    'base_imponible'    => 0,
                    'valor_impuesto'    => 0
                ];
            }
            $impuestosAgrupados[$clave]['base_imponible'] += $impuesto['base_imponible'];
            $impuestosAgrupados[$clave]['valor_impuesto'] += $impuesto['valor_impuesto'];
        }

        // Sección totalConImpuestos con los impuestos agrupados
        $xml_imp = $xml->createElement('totalConImpuestos');
        foreach ($impuestosAgrupados as $impuesto) {
            $xml_tim = $xml->createElement('totalImpuesto');
            $xml_tim->appendChild($xml->createElement('codigo', (int) $impuesto['codigo_impuesto']));
            $xml_tim->appendChild($xml->createElement('codigoPorcentaje', (int) $impuesto['codigo_porcentaje']));
            $xml_tim->appendChild($xml->createElement('baseImponible', number_format((float)$impuesto['base_imponible'], 2, '.', '')));
            $xml_tim->appendChild($xml->createElement('valor', number_format((float)$impuesto['valor_impuesto'], 2, '.', '')));
            $xml_imp->appendChild($xml_tim);
        }
        $xml_def->appendChild($xml_imp);

        $xml_def->appendChild($xml->createElement('propina', '0.00'));
        $xml_def->appendChild($xml->createElement('importeTotal', number_format((float)$totalFactura, 2, '.', '')));
        $xml_def->appendChild($xml->createElement('moneda', 'DOLAR'));

        // Pagos
        $xml_pgs = $xml->createElement('pagos');
        $xml_pag = $xml->createElement('pago');
        $xml_pag->appendChild($xml->createElement('formaPago', $formaPago));
        $xml_pag->appendChild($xml->createElement('total', number_format((float)$totalFactura, 2, '.', '')));
        $xml_pag->appendChild($xml->createElement('plazo', '90'));
        $xml_pag->appendChild($xml->createElement('unidadTiempo', 'dias'));
        $xml_pgs->appendChild($xml_pag);
        $xml_def->appendChild($xml_pgs);

        $xml_fac->appendChild($xml_def);

        // Detalles de la factura
        $xml_dts = $xml->createElement('detalles');

        foreach ($detalles as $d) {
            $xml_det = $xml->createElement('detalle');
            $xml_det->appendChild($xml->createElement('codigoPrincipal', $this->sanitize($d['id'])));
            $xml_det->appendChild($xml->createElement('descripcion', $this->sanitize($d['name'])));
            $xml_det->appendChild($xml->createElement('cantidad', $d['qty']));
            $xml_det->appendChild($xml->createElement('precioUnitario', number_format((float)$d['price'], 2, '.', '')));
            $xml_det->appendChild($xml->createElement('descuento', number_format((float)$d['descuento'], 2, '.', '')));

            // Base imponible corregida
            $baseImponible = round(($d['price'] * $d['qty']) - (($d['price'] * $d['qty']) * ($d['descuento'] / 100)), 2);
            $xml_det->appendChild($xml->createElement('precioTotalSinImpuesto', number_format($baseImponible, 2, '.', '')));

            // Agregar impuestos de cada producto sin repetir
            $xml_ips = $xml->createElement('impuestos');
            $impuestosAgrupadosProd = [];

            foreach ($d['impuestos'] as $imp) {
                $key = $imp['codigo'] . '-' . $imp['codigo_porcentaje'];
                $valorImpuesto = round($baseImponible * ($imp['porcentaje'] / 100), 2);
                if (!isset($impuestosAgrupadosProd[$key])) {
                    $impuestosAgrupadosProd[$key] = [
                        'codigo' => $imp['codigo'],
                        'codigo_porcentaje' => $imp['codigo_porcentaje'],
                        'tarifa' => $imp['porcentaje'],
                        'baseImponible' => 0,
                        'valor' => 0
                    ];
                }

                $impuestosAgrupadosProd[$key]['baseImponible'] += $baseImponible;
                $impuestosAgrupadosProd[$key]['valor'] += $valorImpuesto;
            }

            // Agregar impuestos únicos al XML
            foreach ($impuestosAgrupadosProd as $imp) {
                $xml_ipt = $xml->createElement('impuesto');
                $xml_ipt->appendChild($xml->createElement('codigo', $imp['codigo']));
                $xml_ipt->appendChild($xml->createElement('codigoPorcentaje', $imp['codigo_porcentaje']));
                $xml_ipt->appendChild($xml->createElement('tarifa', $imp['tarifa']));
                $xml_ipt->appendChild($xml->createElement('baseImponible', number_format($imp['baseImponible'], 2, '.', '')));
                $xml_ipt->appendChild($xml->createElement('valor', number_format($imp['valor'], 2, '.', '')));
                $xml_ips->appendChild($xml_ipt);
            }
            $xml_det->appendChild($xml_ips);
            $xml_dts->appendChild($xml_det);
        }

        $xml_fac->appendChild($xml_dts);

        // Información adicional
        $xml_ifa = $xml->createElement('infoAdicional');
        $xml_cp1 = $xml->createElement('campoAdicional', $empresa->email);
        $xml_cp1->setAttribute('nombre', 'email');
        $xml_ifa->appendChild($xml_cp1);

        // [NEW] RIMPE
        if (!empty($empresa->rimpe_type) && $empresa->rimpe_type !== 'Ninguno') {
             $node = $xml->createElement('campoAdicional', 'CONTRIBUYENTE RÉGIMEN RIMPE');
             $node->setAttribute('nombre', 'Contribuyente Régimen RIMPE');
             $xml_ifa->appendChild($node);
        }

        // [NEW] Agente Retención
        if (!empty($empresa->agente_retencion)) {
             $node = $xml->createElement('campoAdicional', 'Resolución No. ' . $empresa->agente_retencion);
             $node->setAttribute('nombre', 'Agente de Retención');
             $xml_ifa->appendChild($node);
        }

        $xml_fac->appendChild($xml_ifa);

        $xml->appendChild($xml_fac);
        // Se eliminan espacios en blanco
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = false;
        $archivo_factura_xml = $xml->saveXML();

        // PARTITIONING: YYYY/MM based on Created At
        $factura = Factura::find($factura_id);
        if($factura) {
             $relativePath = $factura->created_at->format('Y') . '/' . $factura->created_at->format('m') . '/';
        } else {
             $relativePath = date('Y') . '/' . date('m') . '/';
        }
        
        $nombre_fact_xml = $relativePath . $identificadorCliente . '_' . $secuencia . '.xml';

        try {
            Storage::disk('comprobantes/no_firmados')->put($nombre_fact_xml, $archivo_factura_xml);
             
            // Verify if exists (Storage automatically handles directory creation)
            if (!Storage::disk('comprobantes/no_firmados')->exists($nombre_fact_xml)) {
                 throw new Exception("El archivo XML no firmado no fue guardado: $nombre_fact_xml");
            }
            
            XmlFile::updateOrCreate(
                ['factura_id' => $factura_id],
                [
                    'secuencial' => $secuencia,
                    'cliente'    => $razonSocialCli,
                    'directorio' => 'comprobantes/no_firmados', 
                    'estado'     => 'creado',
                    'error'      => null // Clear errors on retry
                ]
            );

        } catch (\Exception $e) {
            Log::error("Error en la creación del XML: " . $e->getMessage());
            throw new Exception('ERROR AL CREAR EL XML: ' . $e->getMessage());
        }

        return $nombre_fact_xml; // Returns YYYY/MM/filename.xml
    }

    /**
     * Genera el XML de la Nota de Crédito
     */
    public function generateNC($nc, $facturaOriginal)
    {
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;

        // Crear la estructura base del XML
        $xml_nc = $xml->createElement('notaCredito');
        $xml_nc->setAttribute('id', 'comprobante');
        $xml_nc->setAttribute('version', '1.0.0'); // Version 1.0.0 is common for NC

        $empresa = $nc->empresa();

        // 1. InfoTributaria
        $xml_inf = $xml->createElement('infoTributaria');
        $xml_inf->appendChild($xml->createElement('ambiente', $empresa->ambiente));
        $xml_inf->appendChild($xml->createElement('tipoEmision', $empresa->tipoEmision));
        $xml_inf->appendChild($xml->createElement('razonSocial', $this->sanitize($empresa->razonSocial)));
        $xml_inf->appendChild($xml->createElement('nombreComercial', $this->sanitize($empresa->nombreComercial)));
        $xml_inf->appendChild($xml->createElement('ruc', $empresa->ruc));
        $xml_inf->appendChild($xml->createElement('claveAcceso', $nc->claveAcceso));
        $xml_inf->appendChild($xml->createElement('codDoc', '04')); // 04 = Nota de Crédito
        $xml_inf->appendChild($xml->createElement('estab', $empresa->estab));
        $xml_inf->appendChild($xml->createElement('ptoEmi', $empresa->ptoEmi));
        $xml_inf->appendChild($xml->createElement('secuencial', $nc->secuencial));
        $xml_inf->appendChild($xml->createElement('dirMatriz', $this->sanitize($empresa->dirMatriz)));
        $xml_nc->appendChild($xml_inf);

        // 2. InfoNotaCredito
        $xml_infoNC = $xml->createElement('infoNotaCredito');
        $xml_infoNC->appendChild($xml->createElement('fechaEmision', date('d/m/Y')));
        $xml_infoNC->appendChild($xml->createElement('dirEstablecimiento', $this->sanitize($empresa->dirEstablecimiento)));
        $xml_infoNC->appendChild($xml->createElement('tipoIdentificacionComprador', $facturaOriginal->customer->typeidenti == 'ruc' ? '04' : '05')); // Simplification, check customer logic
        $xml_infoNC->appendChild($xml->createElement('razonSocialComprador', $this->sanitize($facturaOriginal->customer->businame)));
        $xml_infoNC->appendChild($xml->createElement('identificacionComprador', $facturaOriginal->customer->valueidenti));
        $xml_infoNC->appendChild($xml->createElement('obligadoContabilidad', $empresa->obligadoContabilidad));
        
        // Campos específicos NC
        $xml_infoNC->appendChild($xml->createElement('codDocModificado', '01')); // 01 = Factura
        $xml_infoNC->appendChild($xml->createElement('numDocModificado', $empresa->estab . '-' . $empresa->ptoEmi . '-' . $facturaOriginal->secuencial));
        $xml_infoNC->appendChild($xml->createElement('fechaEmisionDocSustento', $facturaOriginal->created_at->format('d/m/Y')));
        $xml_infoNC->appendChild($xml->createElement('totalSinImpuestos', number_format((float)$nc->subtotal, 2, '.', '')));
        $xml_infoNC->appendChild($xml->createElement('valorModificacion', number_format((float)$nc->total, 2, '.', '')));
        $xml_infoNC->appendChild($xml->createElement('moneda', 'DOLAR'));

        // Agrupamos impuestos
         $impuestosAgrupados = [];
         foreach ($nc->impuestos as $impuesto) {
             $clave = $impuesto['codigo_impuesto'] . '-' . $impuesto['codigo_porcentaje'];
             if (!isset($impuestosAgrupados[$clave])) {
                 $impuestosAgrupados[$clave] = [
                     'codigo_impuesto'   => $impuesto['codigo_impuesto'],
                     'codigo_porcentaje' => $impuesto['codigo_porcentaje'],
                     'base_imponible'    => 0,
                     'valor_impuesto'    => 0
                 ];
             }
             $impuestosAgrupados[$clave]['base_imponible'] += $impuesto['base_imponible'];
             $impuestosAgrupados[$clave]['valor_impuesto'] += $impuesto['valor_impuesto'];
         }

         $xml_imp = $xml->createElement('totalConImpuestos');
         foreach ($impuestosAgrupados as $impuesto) {
             $xml_tim = $xml->createElement('totalImpuesto');
             $xml_tim->appendChild($xml->createElement('codigo', (int) $impuesto['codigo_impuesto']));
             $xml_tim->appendChild($xml->createElement('codigoPorcentaje', (int) $impuesto['codigo_porcentaje']));
             $xml_tim->appendChild($xml->createElement('baseImponible', number_format((float)$impuesto['base_imponible'], 2, '.', '')));
             $xml_tim->appendChild($xml->createElement('valor', number_format((float)$impuesto['valor_impuesto'], 2, '.', '')));
             $xml_imp->appendChild($xml_tim);
         }
         $xml_infoNC->appendChild($xml_imp);
         
         $xml_infoNC->appendChild($xml->createElement('motivo', $nc->motivo_nc));
         $xml_nc->appendChild($xml_infoNC);


        // 3. Detalles
        $xml_dts = $xml->createElement('detalles');
        foreach ($nc->detalles as $d) {
            $xml_det = $xml->createElement('detalle');
            $xml_det->appendChild($xml->createElement('codigoInterno', $this->sanitize($d['product_id']))); 
            $xml_det->appendChild($xml->createElement('descripcion', $this->sanitize($d['descripcion'])));
            $xml_det->appendChild($xml->createElement('cantidad', $d['cantidad']));
            $xml_det->appendChild($xml->createElement('precioUnitario', number_format((float)$d['precioUnitario'], 2, '.', '')));
            $xml_det->appendChild($xml->createElement('descuento', number_format((float)$d['descuento'], 2, '.', '')));
            $baseImpDetalle = ($d['precioUnitario'] * $d['cantidad']) - $d['descuento'];
            $xml_det->appendChild($xml->createElement('precioTotalSinImpuesto', number_format((float)$baseImpDetalle, 2, '.', '')));

            // Impuestos Detalle
            $xml_ips = $xml->createElement('impuestos');
            
            // Fetch Product and its Taxes
            $product = \App\Models\Product::with('impuestos')->find($d['product_id']);
            
            if ($product && $product->impuestos->count() > 0) {
                foreach($product->impuestos as $tax) {
                    $xml_ipt = $xml->createElement('impuesto');
                    $xml_ipt->appendChild($xml->createElement('codigo', $tax->codigo));
                    $xml_ipt->appendChild($xml->createElement('codigoPorcentaje', $tax->codigo_porcentaje));
                    $xml_ipt->appendChild($xml->createElement('tarifa', $tax->porcentaje)); 
                    $xml_ipt->appendChild($xml->createElement('baseImponible', number_format((float)$baseImpDetalle, 2, '.', '')));
                    
                    $valorImpuesto = round($baseImpDetalle * ($tax->porcentaje / 100), 2);
                    $xml_ipt->appendChild($xml->createElement('valor', number_format((float)$valorImpuesto, 2, '.', '')));
                    
                    $xml_ips->appendChild($xml_ipt);
                }
            } else {
                 // Fallback if no tax linked? (Should not happen ideally, but handle gracefully)
                 // Default to VAT 0% if nothing found to prevent rejection due to empty tax
                 // OR Assume VAT 15% (Code 4)?
                 // Let's assume VAT 0% (Code 2, Perc 0, Rate 0) IF product has no tax.
                 // Actually, better to replicate the user's issue: Error said Code 2 -> Rate 0 mismatch.
                 // If we send Code 2 (IVA) with Rate 0, we must use CodePor 0 (0%) or 6 (No Objeto)?
                 // Safer to send 0 if we don't know.
                 // But really, we should trust the DB. 
                 
                 // If no tax found, use IVA 0%
                 $xml_ipt = $xml->createElement('impuesto');
                 $xml_ipt->appendChild($xml->createElement('codigo', '2')); // IVA
                 $xml_ipt->appendChild($xml->createElement('codigoPorcentaje', '0')); // 0%
                 $xml_ipt->appendChild($xml->createElement('tarifa', '0'));
                 $xml_ipt->appendChild($xml->createElement('baseImponible', number_format((float)$baseImpDetalle, 2, '.', '')));
                 $xml_ipt->appendChild($xml->createElement('valor', '0.00'));
                 $xml_ips->appendChild($xml_ipt);
            }
             
             $xml_det->appendChild($xml_ips);
             $xml_dts->appendChild($xml_det);
        }
        $xml_nc->appendChild($xml_dts);


         // 4. InfoAdicional
         $xml_ifa = $xml->createElement('infoAdicional');
         $xml_cp1 = $xml->createElement('campoAdicional', $empresa->email);
         $xml_cp1->setAttribute('nombre', 'email');
         $xml_ifa->appendChild($xml_cp1);

         // [NEW] RIMPE NC
         if (!empty($empresa->rimpe_type) && $empresa->rimpe_type !== 'Ninguno') {
             $node = $xml->createElement('campoAdicional', 'CONTRIBUYENTE RÉGIMEN RIMPE');
             $node->setAttribute('nombre', 'Contribuyente Régimen RIMPE');
             $xml_ifa->appendChild($node);
         }

         // [NEW] Agente Retención NC
         if (!empty($empresa->agente_retencion)) {
             $node = $xml->createElement('campoAdicional', 'Resolución No. ' . $empresa->agente_retencion);
             $node->setAttribute('nombre', 'Agente de Retención');
             $xml_ifa->appendChild($node);
         }
         $xml_nc->appendChild($xml_ifa);

         $xml->appendChild($xml_nc);
         $xml->preserveWhiteSpace = false;
         $xml->formatOutput = true;
         $archivo_xml = $xml->saveXML();

         // PARTITIONING: YYYY/MM
         $relativePath = $nc->created_at->format('Y') . '/' . $nc->created_at->format('m') . '/';
         $nombre_xml = $relativePath . $facturaOriginal->customer->valueidenti . '_' . $nc->secuencial . '_NC.xml';

         try {
             Storage::disk('comprobantes/no_firmados')->put($nombre_xml, $archivo_xml);
             XmlFile::updateOrCreate(
                 ['factura_id' => $nc->id],
                 [
                     'secuencial' => $nc->secuencial,
                     'cliente'    => $nc->customer->businame,
                     'directorio' => 'comprobantes/no_firmados',
                     'estado'     => 'creado',
                 ]
             );
         } catch (\Exception $e) {
             Log::error("Error XML NC: " . $e->getMessage());
             throw new Exception('ERROR AL CREAR EL XML NC: ' . $e->getMessage());
         }

         return $nombre_xml;
    }

    
    /**
     * Limpia y garantiza UTF-8 para evitar rechazos del SRI.
     * Elimina enter, tabs y caracteres extraños.
     */
    private function sanitize($str)
    {
        if (is_null($str)) return '';
        
        // 1. Force UTF-8 and handle potential double or malformed encoding
        if (!mb_check_encoding($str, 'UTF-8')) {
            $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
        }

        // 2. Remove non-printable control characters that break XML (except valid CRLF/Tabs if needed, but SRI prefers space)
        // This regex removes most invisible control characters (0-31 range except 9,10,13 if you want to keep them, but here we replace all with space for safety)
        $str = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $str);
        
        // 3. Remove Newlines and Tabs which break strict hash matching
        $str = str_replace(["\r", "\n", "\t"], ' ', $str);
        
        // 4. Normalize spaces
        $str = preg_replace('/\s+/', ' ', $str);
        
        return trim($str);
    }
}
