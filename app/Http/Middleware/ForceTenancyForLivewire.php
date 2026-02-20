<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Illuminate\Support\Facades\Log;

class ForceTenancyForLivewire
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Debug Log only for Livewire routes
        if ($request->is('livewire/*')) {
             // Check if it's upload OR standard message endpoint matches widely
             if ($request->is('livewire/*')) {
                // Check if NOT central
                if (!in_array($request->getHost(), config('tenancy.central_domains', []))) {
                    
                    // Logic: we want to run Tenancy Init.
                    // But InitializeTenancyByDomain is a middleware itself that calls $next.
                    // We can wrap $next in it.
                    
                    Log::debug('ForceTenancyForLivewire: Initializing Tenancy for ' . $request->getHost());
                    
                    try {
                        $res = app(InitializeTenancyByDomain::class)->handle($request, $next);
                        
                        // Diagnostics for ALL Livewire routes
                        $tmpPath = storage_path('app/livewire-tmp');
                        $appPath = storage_path('app');
                        Log::info('ForceTenancyForLivewire [' . $request->path() . '] DIAGNOSTICS:');
                        Log::info(' - Tenant: ' . (tenant('id') ?? 'NONE'));
                        Log::info(' - App Root: ' . $appPath . ' [exists:' . (file_exists($appPath)?'Y':'N') . ', writable:' . (is_writable($appPath)?'Y':'N') . ']');
                        Log::info(' - Tmp Folder: ' . $tmpPath . ' [exists:' . (file_exists($tmpPath)?'Y':'N') . ', writable:' . (is_writable($tmpPath)?'Y':'N') . ']');
                        Log::info(' - Config Disk: ' . config('livewire.temporary_file_upload.disk', 'null'));
                        Log::info(' - Current PHP User: ' . get_current_user());
                        
                        return $res;
                    } catch (\Throwable $e) {
                        Log::error('ForceTenancyForLivewire CRASH: ' . $e->getMessage());
                        Log::error($e->getTraceAsString());
                        throw $e;
                    }
                }
             }
        }

        return $next($request);
    }
}
