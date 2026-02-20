<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

class SmartTenancyInit
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
        // Check if the current host is a central domain
        if (in_array($request->getHost(), config('tenancy.central_domains', []))) {
            return $next($request);
        }

        // If not central, we assume it's a tenant (or let the Tenancy middleware decide/fail)
        // We delegate to the standard InitializeTenancyByDomain middleware
        // This ensures proper tenant identification and environment setup (storage_path, db, etc.)
        return app(InitializeTenancyByDomain::class)->handle($request, $next);
    }
}
