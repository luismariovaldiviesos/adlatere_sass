<?php

namespace App\Services;

use App\Models\XmlFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class SriSoapService
{
    /**
     * Envía el comprobante al SRI (Recepción).
     */
    public function enviarAlSri($invoiceObj, $factura_id)
    {
        // Detect environment from Access Key (digit 24)
        // Access Key structure: 23 chars (Date..Env) + 1 char (Env is at pos 23, i.e., 24th char)
        // Actually, mapped: 10-22 is RUC, 23 is Env.
        // Let's rely on invoice Obj key if available, otherwise fallback to settings.
        $key = $invoiceObj->key ?? '';
        if (strlen($key) === 49) {
             $envFromKey = substr($key, 23, 1);
             $host = ($envFromKey == '1') ? 'https://celcer.sri.gob.ec' : 'https://cel.sri.gob.ec';
        } else {
             $empresa = empresa();
             $ambiente = $empresa->ambiente ?? '1';
             $host = ($ambiente == '1') ? 'https://celcer.sri.gob.ec' : 'https://cel.sri.gob.ec';
        }
        
        $url = $host . '/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';

        $xml_content = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ec="http://ec.gob.sri.ws.recepcion">
                <soapenv:Header/>
                <soapenv:Body>
                <ec:validarComprobante>
                    <xml>' . $invoiceObj->base64 . '</xml>
                </ec:validarComprobante>
            </soapenv:Body>
            </soapenv:Envelope>';

        $response = $this->sendCurl($url, $xml_content);
        
        // Guardar respuesta raw para debug con ID de Factura
        $name = pathinfo($invoiceObj->signedFileName, PATHINFO_FILENAME);
        Storage::put("debug/respuesta_sri_recepcion_{$name}.xml", $response['body']);
        
        if ($response['code'] !== 200) {
            $this->updateEstado($factura_id, 'comprobantes/no_enviados', 'no_enviado', "Error HTTP {$response['code']}");
            
            $bodyRaw = $response['body'] ?? '';
            $friendlyError = "NO SE ENVIÓ EL COMPROBANTE AL SRI. CÓDIGO: " . $response['code'];

            // Mapeo de errores conocidos del SRI
            if (strpos($bodyRaw, 'GenericJDBCException') !== false || strpos($bodyRaw, 'Could not open connection') !== false) {
                $friendlyError = "SRI FUERA DE LINEA (Error Interno de Base de Datos SRI - 500). Intente más tarde.";
            } elseif ($response['code'] === 0) {
                 $friendlyError = "ERROR DE CONEXIÓN (SRI Inaccesible/Timeout).";
            } else {
                 $friendlyError .= ($bodyRaw) ? " | Detalle: " . substr(strip_tags($bodyRaw), 0, 200) : "";
            }

            throw new Exception($friendlyError);
        }

        // Intentar parsear XML (posiblemente con encoding issues)
        $cleanBody = $response['body'];
        
        try {
            $simpleXml = new \SimpleXMLElement($cleanBody);
        } catch (\Exception $e) {
             // Fallback: Try utf8_encode 
             try {
                 $simpleXml = new \SimpleXMLElement(utf8_encode($cleanBody));
             } catch (\Exception $e2) {
                 throw new Exception("Error crítico parseando respuesta SRI (no es XML válido): " . $e->getMessage());
             }
        }

        $nodes = $simpleXml->xpath('//estado');
        $estado = isset($nodes[0]) ? (string)$nodes[0] : '';

        if ($estado === 'DEVUELTA') {
            $compNodes = $simpleXml->xpath('//comprobante');
            $comprobante = $compNodes[0] ?? null;
            
            $mensajeError = '';
            $infoAdicional = '';
            
            $mensajesAll = [];
            if ($comprobante && isset($comprobante->mensajes->mensaje)) {
                foreach ($comprobante->mensajes->mensaje as $msgObj) {
                    $m = optional($msgObj->mensaje)->__toString();
                    $i = optional($msgObj->informacionAdicional)->__toString();
                    $mensajesAll[] = trim($m . ' - ' . $i);
                }
            }
            
            // Join all messages for logging and display
            $errorFull = implode(' | ', $mensajesAll);

            // AUTO-RECOVERY: Check against ALL messages
            // If SRI says it's already registered or processing in ANY of the messages, we treat it as "RECIBIDA"
            if (str_contains(strtoupper($errorFull), 'CLAVE ACCESO REGISTRADA') || 
                str_contains(strtoupper($errorFull), 'CLAVE DE ACCESO EN PROCESAMIENTO') ||
                str_contains(strtoupper($errorFull), 'EN PROCESAMIENTO') ||
                str_contains(strtoupper($errorFull), 'VALOR DEVUELTO POR EL PROCEDIMIENTO')) {
                 Log::warning("SRI: $errorFull. Tratando como RECIBIDA para verificar autorización.");
                 // Proceed to mark as Sent
                 Storage::disk('comprobantes/enviados')->put($invoiceObj->signedFileName, $invoiceObj->xmlContent);
                 $this->updateEstado($factura_id, 'comprobantes/enviados', 'enviado');
                 return true; 
            }

            $this->updateEstado($factura_id, 'comprobantes/devueltos', 'devuelto', $errorFull);
            
            // Guardar XML devuelto
            Storage::disk('comprobantes/devueltos')->put($invoiceObj->signedFileName, $invoiceObj->xmlContent);

            throw new Exception("SRI DEVOLVIÓ EL COMPROBANTE: " . $errorFull);
        }

        // Si llegamos aquí, fue RECIBIDA
        Storage::disk('comprobantes/enviados')->put($invoiceObj->signedFileName, $invoiceObj->xmlContent);
        $this->updateEstado($factura_id, 'comprobantes/enviados', 'enviado');

        return true; 
    }

    /**
     * Consulta la autorización del comprobante (Autorización).
     */
    public function consultarAutorizacion($invoiceObj, $factura_id)
    {
        // Detect environment from Access Key
        $key = $invoiceObj->key ?? '';
        if (strlen($key) === 49) {
             $envFromKey = substr($key, 23, 1);
             $host = ($envFromKey == '1') ? 'https://celcer.sri.gob.ec' : 'https://cel.sri.gob.ec';
        } else {
             $empresa = empresa();
             $ambiente = $empresa->ambiente ?? '1';
             $host = ($ambiente == '1') ? 'https://celcer.sri.gob.ec' : 'https://cel.sri.gob.ec';
        }
        
        $url = $host . '/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';

        $xml_content = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ec="http://ec.gob.sri.ws.autorizacion">
       <soapenv:Header/>
        <soapenv:Body>
            <ec:autorizacionComprobante>
                <claveAccesoComprobante>' . $invoiceObj->key . '</claveAccesoComprobante>
              </ec:autorizacionComprobante>
            </soapenv:Body>
          </soapenv:Envelope>';

        $response = $this->sendCurl($url, $xml_content);
        
        // Guardar respuesta raw
        $name = pathinfo($invoiceObj->signedFileName, PATHINFO_FILENAME);
        Storage::put("debug/respuesta_sri_autorizacion_{$name}.xml", $response['body']);

        if ($response['code'] !== 200) {
            $errorDetail = ($response['body']) ? " | Detalle: " . substr(strip_tags($response['body']), 0, 200) : "";
            $errorMsg = ($response['code'] === 0) ? "ERROR COMUNICACIÓN SRI (AUTORIZACIÓN): " . $response['body'] : "SRI CAÍDO EN AUTORIZACIÓN. CÓDIGO: " . $response['code'] . $errorDetail;
            throw new Exception($errorMsg);
        }

        $response_utf8 = utf8_encode($response['body']);
        
        try {
            $simpleXml = new \SimpleXMLElement($response_utf8);
        } catch (\Exception $e) {
             throw new Exception('Error al parsear el XML de respuesta de autorización: ' . $e->getMessage());
        }

        $nodes = $simpleXml->xpath('//estado');
        // Fix: Check if node exists to avoid Undefined array key 0
        $estado = isset($nodes[0]) ? (string)$nodes[0] : null;

        // If no state found, check if it's an empty response (Pending)
        if (!$estado) {
             $numNodes = $simpleXml->xpath('//numeroComprobantes');
             $num = isset($numNodes[0]) ? (int)$numNodes[0] : -1;
             
             if ($num === 0) {
                 return ['estado' => 'EN PROCESO (SRI no devuelve datos aún)'];
             }
             
             return ['estado' => 'RESPUESTA DESCONOCIDA'];
        }

        if ($estado === 'NO AUTORIZADO') {
            $authNodes = $simpleXml->xpath('//autorizacion');
            $comprobante = $authNodes[0] ?? null;
            
            $mensajeError = '';
            $infoAdicional = '';
            
            if ($comprobante && isset($comprobante->mensajes->mensaje[0])) {
                $mensajeError = optional($comprobante->mensajes->mensaje[0]->mensaje)->__toString();
                $infoAdicional = optional($comprobante->mensajes->mensaje[0]->informacionAdicional)->__toString();
            }
            
            $errorFull = trim($mensajeError . ' - ' . $infoAdicional);
            
            // Mover a no autorizados only if we have error details, otherwise it might be pending
            Storage::disk('comprobantes/no_autorizados')->put($invoiceObj->signedFileName, $invoiceObj->xmlContent);
            $this->updateEstado($factura_id, 'comprobantes/no_autorizados', 'no_autorizado', $errorFull);

            throw new Exception("COMPROBANTE NO AUTORIZADO: " . $errorFull);
        }

        if ($estado === 'AUTORIZADO') {
            $authNodes = $simpleXml->xpath('//autorizacion');
            $comprobante = $authNodes[0] ?? null;
            
            if (!$comprobante) {
                // Estado authorized but no node? Weird but handle it
                return ['estado' => 'AUTORIZADO', 'xml' => null, 'mensaje' => 'Autorizado pero XML vacio'];
            }
            
            $numeroAutorizacion = (string) $comprobante->numeroAutorizacion;
            $fechaAutorizacion = (string) $comprobante->fechaAutorizacion;
            $xmlAutorizado = (string) $comprobante->comprobante; // El XML final autorizado

            // Guardar XML autorizado
            // Extraer datos para armar el XML final limpio (Autorizacion + Comprobante)
            $xmlAutorizadoStr = (string) $comprobante->comprobante; // El XML firmado dentro del CDATA
            
            // Generate clean XML
            $cleanXml = $this->generarXmlAutorizado($estado, $numeroAutorizacion, $fechaAutorizacion, $xmlAutorizadoStr);
            
            // Guardar en disco el XML limpio (RIDE friendly)
            // Usamos el nombre firmado para mantener consistencia
            $finalXmlName = $invoiceObj->signedFileName;
            Storage::disk('comprobantes/autorizados')->put($finalXmlName, $cleanXml);
            
            // Actualizar estado
            $this->updateEstado($factura_id, 'comprobantes/autorizados', 'autorizado');

            return [
                'estado' => 'AUTORIZADO',
                'numeroAutorizacion' => $numeroAutorizacion,
                'fechaAutorizacion' => $fechaAutorizacion,
                'xml' => $cleanXml 
            ];
        }

        // En proceso u otros estados
        return ['estado' => $estado];
    }

    /**
     * Consulta el estado actual de una clave de acceso sin afectar archivos.
     * Útil para pre-validación.
     */
    public function consultarEstado($claveAcceso)
    {
        // Detect environment from Access Key
        if (strlen($claveAcceso) === 49) {
             $envFromKey = substr($claveAcceso, 23, 1);
             $host = ($envFromKey == '1') ? 'https://celcer.sri.gob.ec' : 'https://cel.sri.gob.ec';
        } else {
             $empresa = empresa();
             $ambiente = $empresa->ambiente ?? '1';
             $host = ($ambiente == '1') ? 'https://celcer.sri.gob.ec' : 'https://cel.sri.gob.ec';
        }

        $url = $host . '/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';

        $xml_content = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ec="http://ec.gob.sri.ws.autorizacion">
       <soapenv:Header/>
        <soapenv:Body>
            <ec:autorizacionComprobante>
                <claveAccesoComprobante>' . $claveAcceso . '</claveAccesoComprobante>
              </ec:autorizacionComprobante>
            </soapenv:Body>
          </soapenv:Envelope>';

        try {
            $response = $this->sendCurl($url, $xml_content);
            if ($response['code'] !== 200) return ['error' => "HTTP {$response['code']}"];
            
            $simpleXml = new \SimpleXMLElement(utf8_encode($response['body']));
            $nodes = $simpleXml->xpath('//estado');
            $estado = isset($nodes[0]) ? (string)$nodes[0] : null;

            if (!$estado) {
                $numNodes = $simpleXml->xpath('//numeroComprobantes');
                return ((isset($numNodes[0]) && (int)$numNodes[0] === 0)) ? ['estado' => 'NO ENCONTRADO'] : ['estado' => 'PENDIENTE'];
            }

            $mensajes = [];
            $msgNodes = $simpleXml->xpath('//mensajes/mensaje');
            foreach($msgNodes as $m) {
                $mensajes[] = (string)$m->mensaje . ' - ' . (string)$m->informacionAdicional;
            }

            return [
                'estado' => $estado,
                'numeroAutorizacion' => (string)($simpleXml->xpath('//numeroAutorizacion')[0] ?? ''),
                'fechaAutorizacion' => (string)($simpleXml->xpath('//fechaAutorizacion')[0] ?? ''),
                'mensajes' => $mensajes
            ];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function sendCurl($url, $xml_post_string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml',
            'Accept: text/xml',
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Fix: Follow SRI redirects (302)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
             return ['code' => 0, 'body' => $error];
        }

        return ['code' => $httpcode, 'body' => $response];
    }

    private function updateEstado($factura_id, $directorio, $estado, $error = null)
    {
        $xmlFile = XmlFile::where('factura_id', $factura_id)->first();
        if ($xmlFile) {
            $data = [
                'directorio' => $directorio,
                'estado' => $estado
            ];
            if ($error) {
                $data['error'] = substr($error, 0, 250); // Asumiendo que hay columna error o similar
            }
            $xmlFile->update($data);
        }
    }

    public function checkConnection()
    {
        try {
            $empresa = empresa();
            // Default to Test environment if no context (e.g. admin dashboard) or if setting missing
            $ambiente = $empresa->ambiente ?? '1'; 
            $host = ($ambiente == '1') ? 'https://celcer.sri.gob.ec' : 'https://cel.sri.gob.ec';
            $url = $host . '/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($code >= 200 && $code < 400) {
                return ['status' => 'online', 'msg' => 'SRI En Línea', 'code' => $code];
            }
            
            return ['status' => 'offline', 'msg' => "SRI Inaccesible ($code)", 'code' => $code];
            
        } catch (\Exception $e) {
            return ['status' => 'offline', 'msg' => $e->getMessage(), 'code' => 0];
        }
    }

    private function generarXmlAutorizado($estado, $numeroAutorizacion, $fechaAutorizacion, $comprobanteAutorizacion)
    {
        $xml =  new \DOMDocument();
        $xml_autor = $xml->createElement('autorizacion');
        $xml_estad = $xml->createElement('estado', $estado);
        $xml_nauto = $xml->createElement('numeroAutorizacion', $numeroAutorizacion);
        $xml_fauto = $xml->createElement('fechaAutorizacion', $fechaAutorizacion);
        $xml_compr = $xml->createElement('comprobante');
        $xml_autor->appendChild($xml_estad);
        $xml_autor->appendChild($xml_nauto);
        $xml_autor->appendChild($xml_fauto);
        $xml_compr->appendChild($xml->createCDATASection($comprobanteAutorizacion));
        $xml_autor->appendChild($xml_compr);
        $xml->appendChild($xml_autor);
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        
        return $xml->saveXML();
    }
}
