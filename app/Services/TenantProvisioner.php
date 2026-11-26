<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class TenantProvisioner
{
    /**
     * Garantiza que la base del tenant exista y tenga migraciones/seeders.
     * Es idempotente: si la base y las migraciones ya existen, no duplica datos.
     */
    public function ensure(Tenant $tenant): void
    {
        $database = $tenant->database();
        $manager = $database->manager();
        $databaseName = $database->getName();

        if (!$manager->databaseExists($databaseName)) {
            $database->makeCredentials();
            $manager->createDatabase($tenant);

            Log::info('[TENANT_PROVISIONER] Database created', [
                'tenant' => $tenant->getTenantKey(),
                'database' => $databaseName,
            ]);
        }

        Artisan::call('tenants:migrate', [
            '--tenants' => [$tenant->getTenantKey()],
        ]);

        Artisan::call('tenants:seed', [
            '--tenants' => [$tenant->getTenantKey()],
        ]);

        Log::info('[TENANT_PROVISIONER] Migrations and seeders executed', [
            'tenant' => $tenant->getTenantKey(),
            'database' => $databaseName,
        ]);
    }
}
