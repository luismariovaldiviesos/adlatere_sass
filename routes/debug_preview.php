<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\FileUploadConfiguration;

Route::get('/debug-preview-logic/{filename}', function ($filename) {
    $diskName = FileUploadConfiguration::disk();
    $disk = Storage::disk($diskName);
    $path = FileUploadConfiguration::path($filename);
    
    // Manually construct expected path if helper differs
    $manualPath = 'livewire-tmp/' . $filename;
    
    return [
        'disk_name' => $diskName,
        'filename' => $filename,
        'helper_path' => $path,
        'exists_via_helper' => $disk->exists($filename), // FileUploadConfiguration::path might include directory?
        'exists_via_manual' => $disk->exists($manualPath),
        'disk_root' => $disk->path(''),
        'full_path_helper' => $disk->path($filename),
        'full_path_manual' => $disk->path($manualPath),
        'directory_contents' => $disk->allFiles('livewire-tmp'),
    ];
});
