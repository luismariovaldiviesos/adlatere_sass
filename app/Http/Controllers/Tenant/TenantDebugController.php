<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TenantDebugController extends Controller
{
    public function testDb()
    {
        try {
            $dbName = DB::connection()->getDatabaseName();
            $tenantId = tenant('id');
            return "Tenant ID: {$tenantId} <br> Database: {$dbName} <br> Connection: " . DB::getDefaultConnection();
        } catch(\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function debugPreview($filename)
    {
        $disk = 'local'; // Match livewire config
        $path = 'livewire-tmp/' . $filename;
        
        return [
            'tenant_id' => tenant('id'),
            'disk' => $disk,
            'looking_for' => $path,
            'full_path' => Storage::disk($disk)->path($path),
            'exists' => Storage::disk($disk)->exists($path),
            'root_path' => Storage::disk($disk)->path(''),
            'files_in_tmp' => Storage::disk($disk)->files('livewire-tmp'),
        ];
    }

    public function debugSignature()
    {
        try {
            $empresa = empresa(); 
            
            $debug = [
                'empresa_id' => $empresa->id ?? 'null',
                'cert_file_db' => $empresa->cert_file,
                'cert_password_db' => $empresa->cert_password ? '******' : 'MISSING',
                'cert_disk_exists' => Storage::disk('certificados')->exists($empresa->cert_file),
                'cert_disk_path' => Storage::disk('certificados')->path($empresa->cert_file),
                'jar_path' => base_path('storage/jar/dist/firmaComprobanteElectronico.jar'),
                'jar_exists' => file_exists(base_path('storage/jar/dist/firmaComprobanteElectronico.jar')),
            ];
    
            // Mock parameters to see command locally
            $nombre_fact_xml = '2025/12/debug_test.xml';
            
            $baseNameWithRelPath = substr($nombre_fact_xml, 0, -4);
            $archivo_x_firmar =  Storage::disk('comprobantes/no_firmados')->path($baseNameWithRelPath.'.xml');
            $ruta_si_firmados_base =  Storage::disk('comprobantes/firmados')->path('');
            $subDir = dirname($baseNameWithRelPath);
            $fullOutputDir = $ruta_si_firmados_base;
            if ($subDir !== '.' && $subDir !== '') {
                 $fullOutputDir .= $subDir . DIRECTORY_SEPARATOR;
            }
            $onlyFileName = basename($baseNameWithRelPath) . '.xml';
            $certPath = Storage::disk('certificados')->path($empresa->cert_file);
            $certPass = $empresa->cert_password;
            
            $argumentos = "\"$archivo_x_firmar\" \"$fullOutputDir\" \"$onlyFileName\" \"$certPath\" \"$certPass\"";
            $comando = "java -jar \"{$debug['jar_path']}\" $argumentos";
            
            $debug['generated_command'] = $comando;
            
            dd($debug);
            
        } catch (\Exception $e) {
            dd($e->getMessage(), $e->getTraceAsString());
        }
    }

    public function dashboardRedirect()
    {
        return redirect()->route('dash');
    }
}
