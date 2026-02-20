<?php

namespace App\Services;

use App\Models\XmlFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\File;

class SignatureService
{
    /**
     * Firma la factura utilizando una estrategia manual determinista (PHP Nativo).
     * Esta versión está calibrada para ser estructuralmente idéntica a la salida de XAdES-BES 
     * requerida por el SRI, eliminando la dependencia de Java.
     */
    public function firmarFactura($nombre_fact_xml, $factura_id, $empresa)
    {
         // Intentar firma nativa directamente para evitar el error de Java SHA1 en el servidor
         try {
             return $this->firmarFacturaNativa($nombre_fact_xml, $factura_id, $empresa);
         } catch (\Exception $e) {
             Log::error("Firma Nativa falló: " . $e->getMessage());
             
             // Solo como último recurso, intentar el JAR si la nativa falla 
             // (aunque probablemente falle por el error de SHA1 reportado)
             return $this->firmarFacturaJAR($nombre_fact_xml, $factura_id, $empresa);
         }
    }

    public function firmarFacturaNativa($nombre_fact_xml, $factura_id, $empresa)
    {
        // 1. Resolve Paths
        if (is_object($nombre_fact_xml) && isset($nombre_fact_xml->name)) {
            $baseNameWithRelPath = $nombre_fact_xml->name;
        } elseif (is_string($nombre_fact_xml)) {
            $baseNameWithRelPath = str_ends_with($nombre_fact_xml, '.xml') ? substr($nombre_fact_xml, 0, -4) : $nombre_fact_xml;
        } else {
            $baseNameWithRelPath = (string)$nombre_fact_xml;
        }

        $xmlPath = Storage::disk('comprobantes/no_firmados')->path($baseNameWithRelPath . '.xml');
        if (!file_exists($xmlPath)) throw new Exception("XML no encontrado en: $xmlPath");

        // 2. Load Certificate
        $certPath = Storage::disk('certificados')->path($empresa->cert_file);
        if (!file_exists($certPath)) throw new Exception("Certificado no encontrado en: $certPath");
        
        $pkcs12 = file_get_contents($certPath);
        $certs = [];
        if (!openssl_pkcs12_read($pkcs12, $certs, $empresa->cert_password)) {
            throw new Exception("No se pudo leer el certificado P12. Verifique la contraseña.");
        }

        $certificate = $certs['cert'];
        $privateKey = $certs['pkey'];
        
        // Extract certificate details
        $certData = openssl_x509_parse($certificate);
        $certDer = base64_decode(preg_replace('/\-+BEGIN CERTIFICATE\-+|\-+END CERTIFICATE\-+|\n|\r/', '', $certificate));
        
        // SHA1 for Cert Digest (etsi:CertDigest) - XAdES standard
        $certDigestSha1 = base64_encode(sha1($certDer, true));
        
        // Issuer Name (RFC 2253 compliant - reverse order)
        $issuerArr = [];
        $issuerData = array_reverse($certData['issuer']);
        foreach ($issuerData as $k => $v) {
            if (is_array($v)) $v = $v[0]; 
            $issuerArr[] = "$k=$v";
        }
        $issuerName = implode(',', $issuerArr);
        $serialNumber = (string)$certData['serialNumber'];

        // 3. Prepare XML - CLEAN & COMPACT
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false; 
        $dom->formatOutput = false; 
        $dom->load($xmlPath);
        
        $root = $dom->documentElement;
        $root->setAttribute('id', 'comprobante');

        // 4. Unique IDs for Signature elements
        $ts = str_replace('.', '', microtime(true));
        $idSignature = "Signature-" . $ts;
        $idSignatureValue = "SignatureValue-" . $ts;
        $idKeyInfo = "KeyInfo-" . $ts;
        $idSignedProperties = "SignedProperties-" . $ts;
        $idObject = "Object-" . $ts;
        $idReference = "Reference-ID-" . $ts;

        $signingTime = date('Y-m-d\TH:i:sP'); 
        $nsDs = 'http://www.w3.org/2000/09/xmldsig#';
        $nsEtsi = 'http://uri.etsi.org/01903/v1.3.2#';

        // 5. CALCULATE DOCUMENT HASH (BEFORE appending signature node)
        // This avoids the circular dependency where the signature includes a hash of itself.
        $docHash = base64_encode(hash('sha256', $dom->documentElement->C14N(false, false), true));

        // 6. ASSEMBLE SIGNATURE STRUCTURE (With placeholders)
        $signatureNode = $dom->createElementNS($nsDs, 'ds:Signature');
        $signatureNode->setAttribute('Id', $idSignature);
        // Explicitly set namespaces on the signature node
        $signatureNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds', $nsDs);
        $signatureNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:etsi', $nsEtsi);

        // SignedInfo
        $signedInfoNode = $dom->createElementNS($nsDs, 'ds:SignedInfo');
        
        $cm = $dom->createElementNS($nsDs, 'ds:CanonicalizationMethod');
        $cm->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signedInfoNode->appendChild($cm);

        $sm = $dom->createElementNS($nsDs, 'ds:SignatureMethod');
        $sm->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
        $signedInfoNode->appendChild($sm);

        // Reference: SignedProperties
        $refSP = $dom->createElementNS($nsDs, 'ds:Reference');
        $refSP->setAttribute('URI', "#" . $idSignedProperties);
        $refSP->setAttribute('Type', 'http://uri.etsi.org/01903#SignedProperties');
        $refSP->appendChild($dom->createElementNS($nsDs, 'ds:DigestMethod'))->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $dvSP = $dom->createElementNS($nsDs, 'ds:DigestValue', ''); // Placeholder
        $refSP->appendChild($dvSP);
        $signedInfoNode->appendChild($refSP);

        // Reference: KeyInfo
        $refKI = $dom->createElementNS($nsDs, 'ds:Reference');
        $refKI->setAttribute('URI', "#" . $idKeyInfo);
        $refKI->appendChild($dom->createElementNS($nsDs, 'ds:DigestMethod'))->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $dvKI = $dom->createElementNS($nsDs, 'ds:DigestValue', ''); // Placeholder
        $refKI->appendChild($dvKI);
        $signedInfoNode->appendChild($refKI);

        // Reference: Document
        $refDoc = $dom->createElementNS($nsDs, 'ds:Reference');
        $refDoc->setAttribute('Id', $idReference);
        $refDoc->setAttribute('URI', "#comprobante");
        $trans = $dom->createElementNS($nsDs, 'ds:Transforms');
        $trans->appendChild($dom->createElementNS($nsDs, 'ds:Transform'))->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $trans->appendChild($dom->createElementNS($nsDs, 'ds:Transform'))->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $refDoc->appendChild($trans);
        $refDoc->appendChild($dom->createElementNS($nsDs, 'ds:DigestMethod'))->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $refDoc->appendChild($dom->createElementNS($nsDs, 'ds:DigestValue', $docHash)); // REAL HASH
        $signedInfoNode->appendChild($refDoc);

        $signatureNode->appendChild($signedInfoNode);

        // SignatureValue (Placeholder)
        $sigValueNode = $dom->createElementNS($nsDs, 'ds:SignatureValue', '');
        $sigValueNode->setAttribute('Id', $idSignatureValue);
        $signatureNode->appendChild($sigValueNode);

        // KeyInfo
        $keyInfoNode = $dom->createElementNS($nsDs, 'ds:KeyInfo');
        $keyInfoNode->setAttribute('Id', $idKeyInfo);
        $xData = $dom->createElementNS($nsDs, 'ds:X509Data');
        $xCert = $dom->createElementNS($nsDs, 'ds:X509Certificate', base64_encode($certDer));
        $xData->appendChild($xCert);
        $keyInfoNode->appendChild($xData);
        $signatureNode->appendChild($keyInfoNode);

        // Object (SignedProperties)
        $objNode = $dom->createElementNS($nsDs, 'ds:Object');
        $objNode->setAttribute('Id', $idObject);
        $qualProps = $dom->createElementNS($nsEtsi, 'etsi:QualifyingProperties');
        $qualProps->setAttribute('Target', "#" . $idSignature);
        
        $sPropsNode = $dom->createElementNS($nsEtsi, 'etsi:SignedProperties');
        $sPropsNode->setAttribute('Id', $idSignedProperties);
        $ssp = $dom->createElementNS($nsEtsi, 'etsi:SignedSignatureProperties');
        $ssp->appendChild($dom->createElementNS($nsEtsi, 'etsi:SigningTime', $signingTime));
        $sc = $dom->createElementNS($nsEtsi, 'etsi:SigningCertificate');
        $certArr = $dom->createElementNS($nsEtsi, 'etsi:Cert');
        $cd = $dom->createElementNS($nsEtsi, 'etsi:CertDigest');
        $dmCert = $dom->createElementNS($nsDs, 'ds:DigestMethod');
        $dmCert->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
        $dvCert = $dom->createElementNS($nsDs, 'ds:DigestValue', $certDigestSha1);
        $cd->appendChild($dmCert);
        $cd->appendChild($dvCert);
        $is = $dom->createElementNS($nsEtsi, 'etsi:IssuerSerial');
        $is->appendChild($dom->createElementNS($nsDs, 'ds:X509IssuerName', $issuerName));
        $is->appendChild($dom->createElementNS($nsDs, 'ds:X509SerialNumber', $serialNumber));
        $certArr->appendChild($cd);
        $certArr->appendChild($is);
        $sc->appendChild($certArr);
        $ssp->appendChild($sc);
        $sPropsNode->appendChild($ssp);
        $qualProps->appendChild($sPropsNode);
        $objNode->appendChild($qualProps);
        $signatureNode->appendChild($objNode);

        // ATTACH SIGNATURE TO ROOT (After calculating docHash)
        $dom->documentElement->appendChild($signatureNode);

        // 7. CALCULATE INTERNAL HASHES (In final position)
        
        // Hash for KeyInfo (#idKeyInfo)
        $kiHash = base64_encode(hash('sha256', $keyInfoNode->C14N(false, false), true));
        $dvKI->nodeValue = $kiHash;

        // Hash for SignedProperties (#idSignedProperties)
        $spHash = base64_encode(hash('sha256', $sPropsNode->C14N(false, false), true));
        $dvSP->nodeValue = $spHash;

        // 8. FINAL SIGNATURE OF SIGNEDINFO
        $signedInfoC14N = $signedInfoNode->C14N(false, false);
        $sigValue = "";
        if (!openssl_sign($signedInfoC14N, $sigValue, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new Exception("Error al firmar con OpenSSL utilizando SHA256.");
        }
        $sigValueNode->nodeValue = base64_encode($sigValue);

        // 9. Finalize document
        $finalXml = $dom->saveXML();

        // 11. Save and Update DB
        $relativePath = dirname($baseNameWithRelPath);
        $onlyName = basename($baseNameWithRelPath);
        $signedFileName = ($relativePath !== '.' && $relativePath !== '' ? $relativePath . '/' : '') . $onlyName . '.xml';
        
        Storage::disk('comprobantes/firmados')->put($signedFileName, $finalXml);
        
        $xmlFile = XmlFile::where('factura_id', $factura_id)->firstOrFail();
        $xmlFile->update([
            'directorio' => 'comprobantes/firmados',
            'estado'     => 'firmado',
        ]);

        $res = new \stdClass();
        $xmlObj = simplexml_load_string($finalXml);
        $res->key = (string)$xmlObj->infoTributaria->claveAcceso;
        $res->base64 = base64_encode($finalXml);
        $res->xmlContent = $finalXml;
        $res->signedFileName = $signedFileName;
        
        Log::info("Factura #$factura_id firmada exitosamente con PHP Nativo (Bypass Java Error).");
        
        return $res;
    }

    /**
     * Firma la factura utilizando el JAR de firma electrónica (Fallback).
     */
    public function firmarFacturaJAR($nombre_fact_xml, $factura_id, $empresa)
    {
        // 1. Resolve Paths
        if (is_object($nombre_fact_xml) && isset($nombre_fact_xml->name)) {
            $baseNameWithRelPath = $nombre_fact_xml->name;
        } elseif (is_string($nombre_fact_xml)) {
            $baseNameWithRelPath = str_ends_with($nombre_fact_xml, '.xml') ? substr($nombre_fact_xml, 0, -4) : $nombre_fact_xml;
        } else {
            $baseNameWithRelPath = (string)$nombre_fact_xml;
        }

        $archivo_x_firmar = Storage::disk('comprobantes/no_firmados')->path($baseNameWithRelPath . '.xml');
        $ruta_si_firmados_base = Storage::disk('comprobantes/firmados')->path('');
        
        $subDir = dirname($baseNameWithRelPath);
        $fullOutputDir = $ruta_si_firmados_base;
        if ($subDir !== '.' && $subDir !== '') {
            $fullOutputDir .= $subDir . DIRECTORY_SEPARATOR;
        }
        
        if (!File::exists($fullOutputDir)) {
            File::makeDirectory($fullOutputDir, 0755, true, true);
        }

        // 2. Validate Cert
        $certPath = Storage::disk('certificados')->path($empresa->cert_file);
        if (!file_exists($certPath)) {
            throw new Exception("Certificado no encontrado en: $certPath");
        }
        $certPass = $empresa->cert_password;

        $onlyFileName = basename($baseNameWithRelPath) . '.xml';
        
        // 3. Command Preparation
        $jarPath = base_path('storage/jar/dist/firmaComprobanteElectronico.jar');
        if (!file_exists($jarPath)) {
             throw new Exception("JAR de firma no encontrado en: $jarPath");
        }

        $jarDir = dirname($jarPath);
        $jarBaseName = basename($jarPath);

        // SANITIZE PATHS FOR JAVA
        $archivo_x_firmar = str_replace('\\', '/', $archivo_x_firmar);
        $fullOutputDir = str_replace('\\', '/', $fullOutputDir);
        $certPath = str_replace('\\', '/', $certPath);

        if (!str_ends_with($fullOutputDir, '/')) {
             $fullOutputDir .= '/';
        }

        $arg1 = escapeshellarg($archivo_x_firmar);
        $arg2 = escapeshellarg($fullOutputDir);
        $arg3 = escapeshellarg($onlyFileName);
        $arg4 = escapeshellarg($certPath);
        $arg5 = escapeshellarg($certPass);

        $comando = "cd " . escapeshellarg($jarDir) . " && java -jar " . escapeshellarg($jarBaseName) . " $arg1 $arg2 $arg3 $arg4 $arg5";

        try {
            $resp = shell_exec($comando . " 2>&1");
            Log::info("SRI JAR Signature Resp: " . ($resp ?? 'Empty response'));
        } catch (\Exception $e) {
            $this->logError($factura_id, 'Error al ejecutar comando JAR: ' . $e->getMessage());
            throw new Exception('Error al ejecutar comando de firma JAR. ' . $e->getMessage());
        }

        $respuesta = substr(trim($resp ?? ''), 0, 7);

        if ($respuesta === 'FIRMADO') {
            $xmlFile = XmlFile::where('factura_id', $factura_id)->firstOrFail();
            $xmlFile->update([
                'directorio' => 'comprobantes/firmados',
                'estado'     => 'firmado',
            ]);

            $fullPathFirmado = $fullOutputDir . $onlyFileName;
            $xmlContent = file_get_contents($fullPathFirmado);

            $res = new \stdClass();
            $xmlObj = simplexml_load_string($xmlContent);
            $res->key = (string)$xmlObj->infoTributaria->claveAcceso;
            $res->base64 = base64_encode($xmlContent);
            $res->xmlContent = $xmlContent;
            $res->signedFileName = ($subDir !== '.' && $subDir !== '' ? $subDir . '/' : '') . $onlyFileName;
            
            return $res;
        } else {
             throw new Exception('Error al firmar con JAR: ' . $resp);
        }
    }

    private function logError($factura_id, $msg)
    {
        Log::error($msg);
        $xmlFile = XmlFile::where('factura_id', $factura_id)->first();
        if ($xmlFile) {
            $xmlFile->update([
                'directorio' => 'comprobantes/no_firmados',
                'estado' => 'creado',
                'error' => substr($msg, 0, 250)
            ]);
        }
    }
}
