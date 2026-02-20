<?php

namespace App\Http\Controllers\Livewire;

use Illuminate\Http\Request;
use Livewire\Controllers\FilePreviewHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TenancyFilePreviewHandler extends FilePreviewHandler
{
    public function handle($filename)
    {
        // Force use of global disk to bypass tenancy context issues
        $disk = 'livewire_global';

        if (!Storage::disk($disk)->exists('livewire-tmp/' . $filename)) {
             // Fallback: try root if path is somehow different
             if (Storage::disk($disk)->exists($filename)) {
                 return response()->file(Storage::disk($disk)->path($filename));
             }
             
             abort(404, 'File not found in global livewire disk');
        }

        return response()->file(Storage::disk($disk)->path('livewire-tmp/' . $filename));
    }
}
