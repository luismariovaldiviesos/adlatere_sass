<?php

namespace App\Http\Controllers\Livewire;

use Illuminate\Http\Request;
use Livewire\Controllers\FileUploadHandler;
use Illuminate\Support\Facades\Log;

class TenancyFileUploadHandler extends FileUploadHandler
{
    public function handle(Request $request)
    {
        Log::info('TenancyFileUploadHandler: Hit!', [
            'host' => $request->getHost(),
            'tenant' => tenant('id') ?? 'NON-TENANT',
            'storage_path' => storage_path(),
            'default_disk' => config('filesystems.default'),
            'livewire_disk' => config('livewire.temporary_file_upload.disk')
        ]);

        return parent::handle($request);
    }
}
