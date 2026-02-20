<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Exception;

class TenantOffboardingService
{
    /**
     * Genera un respaldo completo del Tenant (Archivos + Base de Datos).
     * Retorna la ruta relativa del archivo ZIP generado.
     */
    /**
     * Genera un respaldo completo del Tenant (Archivos + Base de Datos).
     * Retorna la ruta relativa del archivo ZIP generado.
     */
    public function backup(Tenant $tenant): string
    {
        $tenantId = $tenant->id;
        $backupPath = "backups/tenant_{$tenantId}_" . now()->format('Y-m-d_H-i-s') . ".zip";
        $fullBackupPath = storage_path("app/{$backupPath}");

        \Log::info("Iniciando respaldo para tenant: {$tenantId}. Ruta: {$backupPath}");

        // Asegurar que el directorio de backups exista en disco LOCAL
        if (!Storage::disk('local')->exists('backups')) {
            Storage::disk('local')->makeDirectory('backups');
        }

        $zip = new ZipArchive();
        if ($zip->open($fullBackupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            \Log::error("No se pudo abrir/crear el archivo ZIP en: {$fullBackupPath}");
            throw new Exception("No se pudo crear el archivo ZIP de respaldo.");
        }

        // 1. Agregar Archivos del Tenant
        $tenantDisks = config('tenancy.filesystem.disks', []);
        $suffixBase = config('tenancy.filesystem.suffix_base', 'tenant');
        $tenantSuffix = $suffixBase . $tenantId;

        if (!in_array('local', $tenantDisks)) $tenantDisks[] = 'local';
        if (!in_array('public', $tenantDisks)) $tenantDisks[] = 'public';

        foreach ($tenantDisks as $disk) {
            try {
                // Si suffix_storage_path es true, la carpeta está en storage/tenant<id>/app
                // Pero como estamos en el contexto CENTRAL, Storage::disk($disk)->path('') nos da la ruta global.
                
                $diskRoot = Storage::disk($disk)->path('');
                $tenantFolder = rtrim($diskRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $tenantSuffix;
                
                // Si no existe ahí, buscamos en la raíz de storage (comportamiento de suffix_storage_path)
                if (!is_dir($tenantFolder)) {
                    $potentialFolder = storage_path($tenantSuffix . ($disk === 'local' ? '/app' : ($disk === 'public' ? '/app/public' : '')));
                    if (is_dir($potentialFolder)) {
                        $tenantFolder = $potentialFolder;
                    }
                }

                if (is_dir($tenantFolder)) {
                    \Log::info("Agregando carpeta de disco '{$disk}' al ZIP: {$tenantFolder}");
                    $this->addFolderToZip($zip, $tenantFolder, "files/{$disk}");
                } else {
                    \Log::debug("No existe carpeta para inquilino en disco '{$disk}': {$tenantFolder}");
                }
            } catch (\Exception $e) {
                \Log::warning("Error al procesar disco {$disk} para tenant {$tenantId}: " . $e->getMessage());
            }
        }

        // 2. Dump de la Base de Datos
        $dbConfig = config('database.connections.' . config('database.default'));
        $dbName = 'tenant' . $tenantId; 
        $dumpFile = storage_path("app/backups/tenant_{$tenantId}_db.sql");
        
        $username = $dbConfig['username'] ?? 'root';
        $password = $dbConfig['password'] ?? '';
        $host = $dbConfig['host'] ?? '127.0.0.1';
        
        \Log::info("Intentando dump de BD: {$dbName} en {$dumpFile}");
        
        // Usar comillas simples para la contraseña para evitar problemas con caracteres especiales
        $command = "mysqldump --user=\"{$username}\" --password='{$password}' --host=\"{$host}\" {$dbName} > \"{$dumpFile}\" 2>&1";
        
        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($dumpFile)) {
             $zip->addFile($dumpFile, 'database.sql');
             \Log::info("Dump de BD agregado al ZIP correctamente.");
        } else {
             \Log::error("Fallo mysqldump para {$tenantId}. Código: {$returnVar}. Salida: " . implode("\n", $output));
        }

        $closed = $zip->close();
        \Log::info("ZIP cerrado. Éxito: " . ($closed ? 'SI' : 'NO'));

        if (file_exists($dumpFile)) {
            unlink($dumpFile);
        }

        if (!$closed || !file_exists($fullBackupPath)) {
            \Log::error("El archivo ZIP final no existe o no se pudo cerrar: {$fullBackupPath}");
            throw new Exception("Error al finalizar la creación del archivo de respaldo.");
        }

        return $backupPath;
    }

    /**
     * Elimina permanentemente al Tenant y sus datos.
     */
    public function delete(Tenant $tenant): void
    {
        $tenantId = $tenant->id;
        $suffixBase = config('tenancy.filesystem.suffix_base', 'tenant');
        $tenantSuffix = $suffixBase . $tenantId;
        
        // 1. Eliminar Carpeta Raíz de Tenant (Si usa suffix_storage_path)
        $rootTenantFolder = storage_path($tenantSuffix);
        if (is_dir($rootTenantFolder)) {
            \Log::info("Eliminando carpeta raíz de tenant: {$rootTenantFolder}");
            \Illuminate\Support\Facades\File::deleteDirectory($rootTenantFolder);
        }

        // 2. Eliminar Carpetas en Discos (Por si acaso hay discos externos o sin sufijo global)
        $tenantDisks = config('tenancy.filesystem.disks', []);
        if (!in_array('local', $tenantDisks)) $tenantDisks[] = 'local';
        if (!in_array('public', $tenantDisks)) $tenantDisks[] = 'public';

        foreach ($tenantDisks as $disk) {
            try {
                $diskRoot = Storage::disk($disk)->path('');
                $tenantFolder = rtrim($diskRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $tenantSuffix;

                if (is_dir($tenantFolder)) {
                    \Log::info("Eliminando carpeta en disco {$disk}: {$tenantFolder}");
                    \Illuminate\Support\Facades\File::deleteDirectory($tenantFolder);
                }
            } catch (\Exception $e) {
                \Log::warning("No se pudo eliminar carpeta en disco {$disk} para tenant {$tenantId}: " . $e->getMessage());
            }
        }

        // 3. Eliminar el Tenant (BD Central y disparar eventos)
        $tenant->delete();
    }

    private function addFolderToZip($zip, $folder, $zipFolder) {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folder) + 1);
                $zip->addFile($filePath, $zipFolder . '/' . $relativePath);
            }
        }
    }
    
    private function deleteFolder($folder) {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($folder);
    }
}
