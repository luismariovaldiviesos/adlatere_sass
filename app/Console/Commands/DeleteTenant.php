<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class DeleteTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:delete {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a tenant and their database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(\App\Services\TenantOffboardingService $service)
    {
        $id = $this->argument('id');
        $tenant = Tenant::find($id);

        if (!$tenant) {
            $this->error("Tenant with ID '{$id}' not found.");
            return 1;
        }

        if ($this->confirm("Are you sure you want to delete tenant '{$id}'? This will DROP the database and DELETE all files.", true)) {
            try {
                // Use Offboarding Service to clean up Files + DB
                $service->delete($tenant);
                
                $this->info("Tenant '{$id}' (DB + Files) deleted successfully.");
                return 0;
            } catch (\Exception $e) {
                $this->error("Error deleting tenant: " . $e->getMessage());
                return 1;
            }
        }

        $this->info("Operation cancelled.");
        return 0;
    }
}
