<?php

namespace App\Listeners;

use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Events\TenancyEnded;
use Stancl\Tenancy\Events\TenancyInitialized;

class ConfigurePermissionCache
{
    private static $centralKey;

    public function handle($event)
    {
        self::$centralKey = self::$centralKey ?: config('permission.cache.key');

        $tenant = $event instanceof TenancyInitialized ? $event->tenancy->tenant : null;

        $key = $tenant
            ? self::$centralKey . '.' . $tenant->getTenantKey()
            : self::$centralKey;

        config(['permission.cache.key' => $key]);
        PermissionRegistrar::$cacheKey = $key;

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
