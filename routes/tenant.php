<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PdfController;
use App\Http\Livewire\Arqueos;
use App\Http\Livewire\Asignar;
use App\Http\Livewire\Cajas;
use App\Http\Livewire\Categories;
use App\Http\Livewire\Customers;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\DeletedList;
use App\Http\Livewire\Descuentos;
use App\Http\Livewire\Diario;
use App\Http\Livewire\Facturas;
use App\Http\Livewire\Impuestos;
use App\Http\Livewire\InvoiceList;
use App\Http\Livewire\NotasCredito;
use App\Http\Livewire\Permisos;
use App\Http\Livewire\Products;
use App\Http\Livewire\Reports;
use App\Http\Livewire\Sales;
use App\Http\Livewire\Settings;
use App\Http\Livewire\Users;
use App\Http\Livewire\Roles;
use App\Http\Livewire\XmlFiles;
use App\Http\Livewire\Provincias;
use App\Http\Livewire\Cantones;
use App\Http\Livewire\Unidades;
use App\Http\Livewire\Materias;
use App\Http\Livewire\Procedimientos;
use App\Http\Livewire\Asuntos;
use App\Http\Livewire\Fases;
use App\Http\Livewire\EstadosProcesales;
use App\Http\Livewire\Especialidades;
use App\Http\Livewire\Funcionarios;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// FIX: Explicitly handle Central Domain Livewire requests here to prevent them falling into the generic Tenant catch-all below.
// REMOVED: Managed by web.php now that generic routes have regex constraint.

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'tenant.status', // Check if tenant is active
])->group(function () {

    Route::get('/', function () {
        return redirect('/login');
    });

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    });

    
    Route::middleware(['auth'])->group(function () {

        Route::get('categories', Categories::class)->name('categories');
        Route::get('products', Products::class)->name('products');
        Route::get('customers', Customers::class)->name('customers');
        Route::get('users', Users::class)->name('users');
        Route::get('sales', Sales::class)->name('sales');
        Route::get('reports', Reports::class)->name('reports');
        Route::get('dash', Dashboard::class)->name('dash');
        Route::get('settings', Settings::class)->name('settings');
        Route::get('diarios', Diario::class)->name('diario');
        Route::get('cajas', Cajas::class)->name('cajas');
        Route::get('arqueos', Arqueos::class)->name('arqueos');
        Route::get('roles', Roles::class)->name('roles');
        // Route::get('permisos', Permisos::class)->name('permisos');
        Route::get('asignar', Asignar::class)->name('asignar');
        Route::get('descuentos', Descuentos::class)->name('descuentos');
        Route::get('facturas', Facturas::class)->name('facturas');
    
        Route::get('/descargar-pdf/{factura}', [PdfController::class, 'pdfDowloader'])->name('descargar-pdf');
        Route::get('/descargar-arqueo/{arqueo}', [PdfController::class, 'arqueoDowloader'])->name('descargar-arqueo');
        Route::get('reprocesar', XmlFiles::class)->name('reprocesar');
        Route::get('listadofacturas', InvoiceList::class)->name('listadofacturas');
        Route::get('deletedlist', DeletedList::class)->name('deletedlist');
        Route::get('notascredito', NotasCredito::class)->name('notascredito');
        Route::get('impuestos', Impuestos::class)->name('impuestos');

        Route::get('/dashboard', [App\Http\Controllers\Tenant\TenantDebugController::class, 'dashboardRedirect'])->name('dashboard');
        Route::get('provincias', Provincias::class)->name('provincias');
        Route::get('cantones', Cantones::class)->name('cantones');
        Route::get('unidades', Unidades::class)->name('unidades');
        Route::get('materias', Materias::class)->name('materias');
        Route::get('procedimientos', Procedimientos::class)->name('procedimientos');
        Route::get('asuntos', Asuntos::class)->name('asuntos');
        Route::get('fases-procesales', Fases::class)->name('fases-procesales');
        Route::get('estados-procesales', EstadosProcesales::class)->name('estados-procesales');
        Route::get('especialidades', Especialidades::class)->name('especialidades');
        Route::get('funcionarios', Funcionarios::class)->name('funcionarios');

    });

    Route::get('/test-db', [App\Http\Controllers\Tenant\TenantDebugController::class, 'testDb']);

    require __DIR__.'/auth.php';

    // SUPPORT FOR LIVEWIRE IN TENANTS
    Route::post('/livewire/upload-file', [\Livewire\Controllers\FileUploadHandler::class, 'handle'])
        ->name('livewire.upload-file');

    Route::get('/livewire/preview-file/{filename}', [\App\Http\Controllers\Livewire\TenancyFilePreviewHandler::class, 'handle'])
        ->name('livewire.preview-file')
        ->where('filename', '.*');

    // SERVE TENANT ASSETS (Via Query Param to bypass Nginx Static 404s)
    Route::get('/tenant-media', [\App\Http\Controllers\TenantAssetController::class, 'serve'])
        ->name('tenant.media');

    // DEBUG ROUTES
    Route::get('/debug-preview/{filename}', [App\Http\Controllers\Tenant\TenantDebugController::class, 'debugPreview'])->where('filename', '.*');
    Route::get('/debug-signature', [App\Http\Controllers\Tenant\TenantDebugController::class, 'debugSignature']);


    // Debug Route Removed
});