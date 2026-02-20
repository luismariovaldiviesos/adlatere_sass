<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTenantStatus
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
        // Check if we are in a tenant context
        if (function_exists('tenant') && tenant()) {
            // Check status. 1 = Active, 0 = Suspended. Default to 1 (Active) if missing.
            $status = tenant('status') ?? 1;
            
            if ($status != 1) {
                return response()->view('errors.suspended', [], 403);
            }
        }

        return $next($request);
    }
}
