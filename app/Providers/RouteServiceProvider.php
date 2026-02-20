<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dash';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        // FORCE LIVEWIRE OVERRIDES (Surgery Method)
        try {
            $uploadRoute = \Illuminate\Support\Facades\Route::getRoutes()->getByName('livewire.upload-file');
            if ($uploadRoute) {
                $uploadRoute->uses('App\Http\Controllers\Livewire\TenancyFileUploadHandler@handle');
                // Force middleware reset and add ours
                $uploadRoute->action['middleware'] = []; // Clear existing
                $uploadRoute->middleware(['web', \App\Http\Middleware\SmartTenancyInit::class]);
            }

            $previewRoute = \Illuminate\Support\Facades\Route::getRoutes()->getByName('livewire.preview-file');
            if ($previewRoute) {
                $previewRoute->uses('App\Http\Controllers\Livewire\TenancyFilePreviewHandler@handle');
                $previewRoute->action['middleware'] = []; // Clear existing
                $previewRoute->middleware(['web', \App\Http\Middleware\SmartTenancyInit::class]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to override Livewire routes: ' . $e->getMessage());
        }
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
