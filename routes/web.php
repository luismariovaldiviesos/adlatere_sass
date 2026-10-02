<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
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
// use App\Http\Livewire\Permisos;
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
use App\Http\Livewire\Juicios;
use App\Http\Livewire\Consultas;
use App\Http\Livewire\TiposActividades;
use App\Http\Livewire\PlantillasTiposActividad;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación local.
| Estas rutas son cargadas por el RouteServiceProvider dentro de un grupo que
| contiene el middleware "web".
|
*/

// Redirigir la raíz al login (En local no suele haber landing page)
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de invitados (Login)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    
    // Dashboard (Redirige al Livewire de Dashboard que antes tenías en /dash)
    Route::get('/dashboard', function () {
        return redirect()->route('dash');
    })->name('dashboard');

    // Módulos del Sistema
    Route::get('dash', Dashboard::class)->name('dash');
    Route::get('categories', Categories::class)->name('categories');
    Route::get('products', Products::class)->name('products');
    Route::get('customers', Customers::class)->name('customers');
    Route::get('users', Users::class)->name('users');
    Route::get('sales', Sales::class)->name('sales');
    Route::get('reports', Reports::class)->name('reports');
    Route::get('settings', Settings::class)->name('settings');
    Route::get('diarios', Diario::class)->name('diario');
    Route::get('cajas', Cajas::class)->name('cajas');
    Route::get('arqueos', Arqueos::class)->name('arqueos');
    Route::get('roles', Roles::class)->name('roles');
    // Route::get('permisos', Permisos::class)->name('permisos');
    Route::get('asignar', Asignar::class)->name('asignar');
    Route::get('descuentos', Descuentos::class)->name('descuentos');
    Route::get('facturas', Facturas::class)->name('facturas');

    // Descargas PDF
    Route::get('/descargar-pdf/{factura}', [PdfController::class, 'pdfDowloader'])->name('descargar-pdf');
    Route::get('/descargar-arqueo/{arqueo}', [PdfController::class, 'arqueoDowloader'])->name('descargar-arqueo');
    
    // Otros procesos y listados
    Route::get('reprocesar', XmlFiles::class)->name('reprocesar');
    Route::get('listadofacturas', InvoiceList::class)->name('listadofacturas');
    Route::get('deletedlist', DeletedList::class)->name('deletedlist');
    Route::get('notascredito', NotasCredito::class)->name('notascredito');
    Route::get('impuestos', Impuestos::class)->name('impuestos');

    // Catálogos
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
    Route::get('juicios', Juicios::class)->name('juicios');
    Route::get('consultas', Consultas::class)->name('consultas');
    Route::get('mis-consultas', Consultas::class)->name('mis-consultas');
    Route::get('tipoactividad', TiposActividades::class)->name('tipoactividad');
    Route::get('plantillas-tipos-actividad', PlantillasTiposActividad::class)->name('plantillas-tipos-actividad');
});

// Rutas de autenticación (Laravel Breeze / UI)
require __DIR__.'/auth.php';

// Soporte para carga de archivos de Livewire
Route::post('/livewire/upload-file', [\Livewire\Controllers\FileUploadHandler::class, 'handle'])
    ->name('livewire.upload-file');

// Nota: He reemplazado el TenancyFilePreviewHandler por el handler original de Livewire,
// ya que al no haber tenants, los archivos se manejan de manera estándar.
Route::get('/livewire/preview-file/{filename}', [\Livewire\Controllers\FilePreviewHandler::class, 'handle'])
    ->name('livewire.preview-file')
    ->where('filename', '.*');

    // Ruta para servir archivos media (mantiene compatibilidad con las vistas existentes)
Route::get('/tenant-media', [\App\Http\Controllers\TenantAssetController::class, 'serve'])
    ->name('tenant.media');