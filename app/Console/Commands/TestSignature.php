<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

class TestSignature extends Command
{
    protected $signature = 'test:signature {tenant_id?}';
    protected $description = 'Probar firma electrónica y diagnóstico de Java/OpenSSL';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id') ?? 'empresauno';
        $this->info("Iniciando diagnóstico para Tenant: $tenantId");

        // 1. Check Tenancy
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant no encontrado.");
            return;
        }

        tenancy()->initialize($tenant);
        $this->info("Tenancy inicializado.");

        $config = Setting::first();
        if (!$config) {
            $this->error("No hay configuración (Settings) en este tenant.");
            return;
        }

        $certFile = $config->cert_file;
        $certPass = $config->cert_password;

        $this->info("Archivo Configurado: $certFile");
        $this->info("Password Configurado: " . substr($certPass, 0, 2) . "****");

        // 2. Resolve Path
        // Manually build path to debug Storage logic
        $diskPath = config('filesystems.disks.certificados.root'); // Usually storage_path('app/certificados') or similar
        // Because tenancy changes roots, let's use the Storage facade
        $fullPath = Storage::disk('certificados')->path($certFile);
        
        $this->info("Ruta Resuelta (Laravel): $fullPath");

        if (!file_exists($fullPath)) {
            $this->error("❌ EL ARCHIVO NO EXISTE FÍSICAMENTE EN ESA RUTA.");
            $this->warn("Verifique mayúsculas/minúsculas. Listando directorio:");
            $dir = dirname($fullPath);
            if(is_dir($dir)) {
                $files = scandir($dir);
                foreach($files as $f) {
                     $this->line(" - $f");
                }
            } else {
                $this->error("El directorio padre tampoco existe: $dir");
            }
            return;
        } else {
            $this->info("✅ Archivo encontrado físicamente.");
        }

        // 3. Test OpenSSL
        $this->info("--- Probando OpenSSL (PHP) ---");
        if (!function_exists('openssl_pkcs12_read')) {
            $this->error("❌ La extensión OpenSSL no está habilitada en PHP cli.");
            return;
        } 
        
        $p12 = file_get_contents($fullPath);
        $certs = [];
        if (openssl_pkcs12_read($p12, $certs, $certPass)) {
            $this->info("✅ OpenSSL leyó el archivo correctamente.");
        } else {
             $this->error("❌ OpenSSL falló al leer.");
             return;
        }

        // CONVERSION
        $this->info("--- Convirtiendo P12 (Legacy -> Modern) ---");
        $tempP12Path = sys_get_temp_dir() . '/test_signature_' . uniqid() . '.p12';
        openssl_pkcs12_export($certs['cert'], $tempP12Content, $certs['pkey'], $certPass);
        file_put_contents($tempP12Path, $tempP12Content);
        $this->info("P12 Temporal creado en: $tempP12Path");

        // 4. Test Java
        $this->info("--- Probando Java Runtime ---");
        $javaVer = shell_exec("java -version 2>&1");
        $this->line("Versión Java detectada:");
        $this->line($javaVer);

        // 5. Test JAR Execution
        $this->info("--- Probando JAR ---");
        $jarPath = base_path('storage/jar/dist/firmaComprobanteElectronico.jar');
        if (!file_exists($jarPath)) {
            $this->error("❌ JAR no encontrado en: $jarPath");
            return;
        }

        // Mock XML file
        $xmlName = 'test_signature.xml';
        $xmlPath = Storage::disk('comprobantes/no_firmados')->path($xmlName);
        if (!file_exists(dirname($xmlPath))) mkdir(dirname($xmlPath), 0755, true);
        file_put_contents($xmlPath, '<xml>dummy</xml>');
        
        $outDir = Storage::disk('comprobantes/firmados')->path('');
        if (!file_exists($outDir)) mkdir($outDir, 0755, true);

        // Build Args with escapeshellarg
        $arg1 = escapeshellarg($xmlPath);
        $arg2 = escapeshellarg($outDir);
        $arg3 = escapeshellarg($xmlName);
        $arg4 = escapeshellarg($tempP12Path); // Use CONVERTED Path
        $arg5 = escapeshellarg($certPass);
        
        $securityFile = base_path('storage/java.security');
        
        // ADD FLAG to match Service
        $cmd = "java -Djava.security.properties==".escapeshellarg($securityFile)." -Dorg.jcp.xml.dsig.secureValidation=false -jar ".escapeshellarg($jarPath)." $arg1 $arg2 $arg3 $arg4 $arg5 2>&1";
        
        $this->info("Ejecutando: $cmd");
        $output = shell_exec($cmd);
        
        if (file_exists($tempP12Path)) unlink($tempP12Path); // Cleanup
        
        $this->info("--- Salida del JAR ---");
        $this->line($output);
        
        if (str_contains($output, 'FIRMADO')) {
            $this->info("✅ PRUEBA EXITOSA: El JAR firmó correctamente.");
        } else {
            $this->error("❌ EL JAR FALLÓ.");
        }
    }
}
