<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

Route::get('/debug-config', function() {
    $diskName = Config::get('livewire.temporary_file_upload.disk');
    $diskConfig = Config::get("filesystems.disks.{$diskName}");
    
    // Try to write a test file
    try {
        Storage::disk($diskName)->put('test-debug.txt', 'Hello from debug route');
        $writeSuccess = true;
        $exists = Storage::disk($diskName)->exists('test-debug.txt');
        $path = Storage::disk($diskName)->path('test-debug.txt');
    } catch (\Exception $e) {
        $writeSuccess = false;
        $writeError = $e->getMessage();
    }

    return [
        'livewire_disk_name' => $diskName,
        'disk_config' => $diskConfig,
        'write_success' => $writeSuccess ?? false,
        'file_exists' => $exists ?? false,
        'physical_path' => $path ?? 'N/A',
        'write_error' => $writeError ?? 'None',
        'tenancy_initialized' => tenancy()->initialized,
        'tenant_id' => tenant('id'),
        'base_path' => base_path(),
        'storage_path_global' => storage_path(),
    ];
});
