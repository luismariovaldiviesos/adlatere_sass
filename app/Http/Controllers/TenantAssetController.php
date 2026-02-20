<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantAssetController extends Controller
{
    public function show($path)
    {
        // Security: Prevent accessing files outside of public folder
        if (strpos($path, '..') !== false) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return response()->file($disk->path($path));
        }

        abort(404);
    }

    public function serve(Request $request)
    {
        $path = $request->query('path');

        if (!$path) {
            abort(404);
        }

        return $this->show($path);
    }
}
