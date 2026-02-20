<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Str;

class LinkNgrok extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ngrok:link {tenant_id} {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link a weird Ngrok URL to a tenant domain';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $url = $this->argument('url');

        // Extract host from URL if full URL is provided
        $host = parse_url($url, PHP_URL_HOST) ?? $url;

        // Validation
        if (!$host) {
            $this->error('Invalid URL or Host');
            return 1;
        }

        $this->info("Linking Tenant [{$tenantId}] to Domain [{$host}]...");

        // Ensure secondary domain exists
        // We do NOT want to overwrite the primary local domain if possible, 
        // but for now let's just use updateOrCreate logic or replace the ngrok entry.
        
        // Check if tenant exists
        $tenant = \App\Models\Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant '{$tenantId}' not found!");
            return 1;
        }

        // Logic:
        // 1. Find if this tenant already has a 'ngrok' domain assigned (heuristic: contains 'ngrok')
        // 2. OR create a new one.
        
        $existingNgrok = Domain::where('tenant_id', $tenantId)
            ->where('domain', 'like', '%ngrok%')
            ->first();

        if ($existingNgrok) {
            $existingNgrok->domain = $host;
            $existingNgrok->save();
            $this->info("Updated existing Ngrok domain entry.");
        } else {
            // Check if this specific domain is already taken by another tenant
            $taken = Domain::where('domain', $host)->first();
            if ($taken && $taken->tenant_id !== $tenantId) {
                $this->error("Domain is already assigned to tenant: " . $taken->tenant_id);
                // Force claim?
                if ($this->confirm('Do you want to steal this domain?')) {
                    $taken->tenant_id = $tenantId;
                    $taken->save();
                    $this->info("Domain reassigned.");
                } else {
                    return 0;
                }
            } else {
                // Create new
                Domain::create([
                    'domain' => $host,
                    'tenant_id' => $tenantId
                ]);
                $this->info("Created new Ngrok domain entry.");
            }
        }

        $this->info("Done! Access your tenant at: https://{$host}");
        return 0;
    }
}
