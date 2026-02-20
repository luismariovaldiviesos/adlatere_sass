<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RepairTenantStorage extends Command
{
    protected $signature = 'tenants:repair-storage';
    protected $description = 'Fix missing storage directories for all tenants (including livewire-tmp)';

    public function handle()
    {
        $this->info('Starting Infallible Global and Tenant Storage Repair...');

        // 1. Ensure CENTRAL storage for Livewire exists
        $centralTmp = base_path('storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'livewire-tmp');
        if (!file_exists($centralTmp)) {
            mkdir($centralTmp, 0777, true);
            $this->info('   [√] Created central livewire-tmp');
        } else {
            @chmod($centralTmp, 0777);
            $this->info('   [√] Central livewire-tmp permissions verified (0777)');
        }

        $tenantFolders = [
            'app', 
            'app/livewire-tmp',
            'app/public',
            'app/public/products',
            'app/public/categories',
            'app/certificados',
            'app/comprobantes',
            'app/comprobantes/autorizados',
            'app/comprobantes/pdfs',
            'app/comprobantes/xmlaprobados',
            'app/comprobantes/enviados',
            'app/comprobantes/firmados',
            'app/comprobantes/no_firmados',
            'app/comprobantes/no_autorizados',
            'app/comprobantes/devueltos',
            'app/comprobantes/no_enviados',
            'framework',
            'framework/cache',
            'framework/views',
            'framework/sessions',
            'logs',
        ];

        Tenant::all()->each(function ($tenant) use ($tenantFolders) {
            $this->info("--- FIXING TENANT: {$tenant->id} ---");

            try {
                // 1. Determine physical root (storage/tenantX)
                $tenantRoot = base_path('storage' . DIRECTORY_SEPARATOR . 'tenant' . $tenant->id);
                if (!file_exists($tenantRoot)) {
                    mkdir($tenantRoot, 0777, true);
                    $this->line("   [+] Root created: {$tenantRoot}");
                }
                @chmod($tenantRoot, 0777);

                // 2. CLEANUP: Delete redundant nested app/app or app/livewire-tmp/app etc if it exists
                $redundantApp = $tenantRoot . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'app';
                if (is_dir($redundantApp)) {
                    $this->warn("   [!] Removing redundant nested folder: app/app");
                    $this->recursiveDelete($redundantApp);
                }

                // 3. Create canonical structure and FORCE PERMISSIONS
                foreach ($tenantFolders as $folder) {
                    $path = $tenantRoot . DIRECTORY_SEPARATOR . $folder;
                    if (!file_exists($path)) {
                        if (mkdir($path, 0777, true)) {
                            $this->line("   [√] Folder created: {$folder}");
                        } else {
                            $this->error("   [X] Failed to create: {$folder}");
                        }
                    }
                    
                    // Force permissions recursively if possible, or at least on the item
                    @chmod($path, 0777);
                }

                // 4. Verify writability in context
                $testPath = $tenantRoot . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'livewire-tmp';
                $testFile = $testPath . DIRECTORY_SEPARATOR . 'repair_test.txt';
                if (@file_put_contents($testFile, 'OK') !== false) {
                    $this->line("   [√] Writability Test: OK");
                    @unlink($testFile);
                } else {
                    $this->error("   [X] Writability Test: FAILED in {$testPath}");
                }

                $this->info("Successfully processed: {$tenant->id}");

            } catch (\Exception $e) {
                $this->error("   [!] ERROR: " . $e->getMessage());
            }
        });

        $this->info('Tenant Storage Repair Completed!');
        $this->warn('CRITICAL: Run "chown -R adminfacta:adminfacta storage/" on server to fix ownership issues.');
    }

    private function recursiveDelete($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->recursiveDelete($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
    }
}
