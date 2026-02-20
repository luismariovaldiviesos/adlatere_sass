<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Services\TenantOffboardingService;

class BackupTenant extends Command
{
    protected $signature = 'tenant:backup {id}';
    protected $description = 'Create a full backup (files + database) for a tenant';

    public function handle(TenantOffboardingService $service)
    {
        $id = $this->argument('id');
        $tenant = Tenant::find($id);

        if (!$tenant) {
            $this->error("Tenant '{$id}' not found.");
            return 1;
        }

        $this->info("Starting backup for tenant '{$id}'...");
        
        try {
            $path = $service->backup($tenant);
            $this->info("Backup created successfully!");
            $this->info("Location: " . storage_path("app/" . $path));
            $this->info("Relative: " . $path);
            return 0;
        } catch (\Exception $e) {
            $this->error("Backup failed: " . $e->getMessage());
            return 1;
        }
    }
}
