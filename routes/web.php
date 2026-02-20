<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// FORCE LIVEWIRE ROUTES GLOBALLY (Fixes 404 on Central Domain)
// REMOVED - Using Domain Specific Routes inside loop instead

// Consolidated Central Domains Group to avoid route name collisions and serialization issues
$centralDomains = config('tenancy.central_domains');
$centralRegex = implode('|', array_map(function ($d) {
    return preg_quote($d, '/');
}, $centralDomains));

Route::domain('{domain}')->where(['domain' => $centralRegex])->group(function () {
    Route::get('/', [App\Http\Controllers\CentralController::class, 'index'])->name('central.home');

    Route::get('/dash', \App\Http\Livewire\Admin\Tenants::class)->middleware(['auth'])->name('central.dashboard');
    Route::get('/planes', \App\Http\Livewire\Admin\Plans::class)->middleware(['auth'])->name('central.plans');

    Route::get('/payment/response', [\App\Http\Controllers\PaymentController::class, 'response'])->name('payment.response');
    Route::get('/payment/cancel', [\App\Http\Controllers\PaymentController::class, 'cancel'])->name('payment.cancel');

    // Subscription Renewal Route
    Route::get('/payment/renewal/{tenant}', [\App\Http\Controllers\PaymentController::class, 'createRenewal'])->name('payment.renewal');

    Route::get('/admin/tenants/download', [App\Http\Controllers\CentralController::class, 'downloadTenantFile'])
        ->middleware(['auth'])->name('tenants.download');

    Route::get('/test-db', [App\Http\Controllers\CentralController::class, 'testDb'])->name('central.test-db');

    Route::name('central.')->group(function() {
        require __DIR__.'/auth.php';
    });

    // Redirect standard register to landing page plans section
    Route::get('/register', [App\Http\Controllers\CentralController::class, 'registerRedirect'])->name('register_landing');
});

// DEBUG ROUTES (Extracted to Controller for serialization)
Route::get('/debug-routing', [App\Http\Controllers\CentralController::class, 'debugRouting']);
Route::get('/debug-assets', [App\Http\Controllers\CentralController::class, 'debugAssets']);


// Debug Route removed (moved to tenant.php)
