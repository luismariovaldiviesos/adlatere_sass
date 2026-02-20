<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupXmlDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:xml-duplicates {tenant}';

    protected $description = 'Deletes duplicate XmlFile records for a given tenant, keeping only the latest.';

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $tenant = \App\Models\Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant '$tenantId' not found.");
            return 1;
        }

        $tenant->run(function () {
            $this->info("Cleaning duplicates for tenant: " . tenant('id'));
            
            $groups = \App\Models\XmlFile::all()->groupBy('factura_id');
            $deletedCount = 0;

            foreach ($groups as $facturaId => $files) {
                if ($files->count() > 1) {
                    $this->info("Found {$files->count()} records for Factura ID: $facturaId");
                    
                    // Order by ID descending (keep the latest)
                    $sorted = $files->sortByDesc('id');
                    
                    // The one to keep
                    $keep = $sorted->shift();
                    $this->info("Keeping ID: {$keep->id} (Estado: {$keep->estado})");

                    // Delete the rest
                    foreach ($sorted as $duplicate) {
                        $duplicate->delete();
                        $deletedCount++;
                        $this->warn("Deleted Duplicate ID: {$duplicate->id}");
                    }
                }
            }
            
            $this->info("Total deleted records: $deletedCount");
        });

        return 0;
    }
}
