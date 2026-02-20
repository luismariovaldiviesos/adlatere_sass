<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (DEBUG)
|--------------------------------------------------------------------------
*/

Route::get('/debug-config', function () {
    return [
        'central_domains' => config('tenancy.central_domains'),
        'route_current' => Route::current()->getName(),
        'app_url' => config('app.url'),
    ];
});
