<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class CentralController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function dashboard()
    {
        // This method is called by the central dashboard route
        // We can just rely on the Livewire component being handled by the route declaration
        // but for standard routes that were closures, we use methods here.
    }

    public function downloadTenantFile(Request $request)
    {
        $path = $request->query('path');
        $fullPath = storage_path('app/' . $path);
        
        Log::info("Intento de descarga: " . $path . " (Full: " . $fullPath . ")");

        if (strpos($path, 'backups/') !== false && file_exists($fullPath)) {
            return response()->download($fullPath);
        }
        
        Log::warning("Descarga fallida: Archivo no encontrado o ruta inválida.");
        abort(403, 'Archivo no encontrado o acceso denegado.');
    }

    public function testDb()
    {
        try {
            $dbName = DB::connection()->getDatabaseName();
            return "CENTRAL APP <br> Database: {$dbName} <br> Connection: " . DB::getDefaultConnection();
        } catch(\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function registerRedirect()
    {
        return redirect('/#applications');
    }

    public function debugRouting()
    {
        $routes = Route::getRoutes();
        $livewireRoutes = [];
        foreach ($routes as $route) {
            if (strpos($route->uri(), 'livewire') !== false) {
                $livewireRoutes[] = [
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'methods' => $route->methods(),
                    'domain' => $route->domain(),
                    'action' => $route->getActionName(),
                ];
            }
        }

        return [
            'host' => request()->getHost(),
            'central_domains_config' => config('tenancy.central_domains'),
            'is_central' => in_array(request()->getHost(), config('tenancy.central_domains')),
            'livewire_routes' => $livewireRoutes,
        ];
    }

    public function debugAssets()
    {
        $data = [
            'app.url' => config('app.url'),
            'app.asset_url' => config('app.asset_url'),
            'livewire.asset_url' => config('livewire.asset_url'),
            'request_host' => request()->getHost(),
            'request_scheme' => request()->getScheme(),
            'asset_test' => asset('test.css'),
            'global_asset_exists' => function_exists('global_asset'),
            'global_asset_test' => function_exists('global_asset') ? global_asset('test.css') : 'N/A',
            'proxies' => request()->getCachedClientIps(),
        ];
        dd($data);
    }
}
