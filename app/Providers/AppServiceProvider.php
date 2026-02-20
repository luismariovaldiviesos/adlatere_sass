<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Services\Payments\PaymentGatewayInterface::class,
            \App\Services\Payments\PayphoneService::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // GLOBAL FIX FOR LIVEWIRE & ASSETS (Local & Ngrok)
        // Always force the asset/app URL to match the current request root.
        
        // Register Layout Component
        \Illuminate\Support\Facades\Blade::component('layouts.theme.app', 'theme-layout');

        // This ensures Tenants use their subdomain and Ngrok uses its tunnel URL.
        
        if (!app()->runningInConsole()) {
            $currentRoot = request()->root(); 

            // Safer HTTPS Force (Essential for CloudPanel/Nginx Proxy)
            // Fixes "Too Many Redirects" when Nginx terminates SSL but Laravel doesn't know.
            if (app()->environment('production') || str_contains(request()->header('Host'), 'facta.ec')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            } else if (str_contains(request()->header('Host'), 'ngrok') || request()->header('X-Forwarded-Proto') === 'https') {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }

            config(['app.asset_url' => $currentRoot]);
            config(['livewire.app_url' => $currentRoot]);
            config(['app.url' => $currentRoot]); // FORCE app.url to match current domain
        }

        // FIX: Remove the default global Livewire route name so our Tenant-specific route takes precedence
        // We do this in 'booted' to ensure Livewire has already registered its routes
        $this->app->booted(function () {
            $router = app('router');
            $routes = $router->getRoutes();
            
            // Iterate manually because getByName might fail if there are duplicates
            foreach ($routes as $route) {
                if ($route->getName() === 'livewire.upload-file') {
                    // Check if the domain is NOT our dynamic binding (i.e. if it's the global one)
                    // If my tenant route has dynamic domain, its domain property might be null or dynamic regex
                    // The global one usually has no domain or central domain.
                    
                    // Simple hack: Rename ALL of them to fallback, then re-register ours? 
                    // No, let's just log every occurrence.
                    
                    \Illuminate\Support\Facades\Log::info("Renamer: Found route named 'livewire.upload-file'. Domain: " . ($route->getDomain() ?? 'None'));
                    
                    // Check if the route is a TENANT route by checking its middleware
                    $middlewares = $route->gatherMiddleware();
                    $isTenantRoute = false;
                    foreach ($middlewares as $mw) {
                        if (str_contains($mw, 'InitializeTenancyByDomain')) {
                            $isTenantRoute = true;
                            break;
                        }
                    }

                    if (!$isTenantRoute) {
                         \Illuminate\Support\Facades\Log::info("Renamer: Renaming GLOBAL route to fallback.");
                         $route->name('livewire.upload-file.global_fallback');
                    } else {
                         \Illuminate\Support\Facades\Log::info("Renamer: Skipping TENANT route.");
                    }
                }
            }
        });

    }
}
