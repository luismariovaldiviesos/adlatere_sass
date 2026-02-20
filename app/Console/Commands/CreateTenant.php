<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {id} {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant with a domain';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        $domain = $this->argument('domain');

        $this->info("Creating tenant {$id} with domain {$domain}...");

        try {
            $tenant = Tenant::create(['id' => $id]);
            $tenant->domains()->create(['domain' => $domain]);
            
            $this->info("Tenant created successfully!");
            $this->info("Database: tenant{$id}");
            $this->info("URL: http://{$domain}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error creating tenant: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
