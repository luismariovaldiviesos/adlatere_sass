<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Factura;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;


class Diario extends Component
{

    public $numVentas, $totVentas, $day, $clientes, $salesByPaymentMethod_Data = [];
    
    // Properties for "Important Notes" Alerts
    public $pendingInvoicesCount = 0;
    public $signatureDaysLeft = null;
    public $lowStockCount = 0;
    public $openBoxesCount = 0;
    public $salesGrowth = 0;
    public $reprocesarCount = 0;

    public function render()
    {
        $this->getDiario();
        return view('livewire.diarios.component')
            ->layout('layouts.theme.app');
    }


    public function getDiario()
    {

        $from = Carbon::now()->format('Y-m-d') . ' 00:00:00';
        $to = Carbon::now()->format('Y-m-d') . ' 23:59:59';
        
        $query = Factura::whereBetween('created_at', [$from, $to])
                        ->whereNotNull('numeroAutorizacion'); // Only authorized
        
        if (Auth()->user()->profile != 'Admin') { // si no es admin
             $query->where('user_id', Auth()->user()->id);
        }

        $this->clientes = Customer::all()->count();
        $this->numVentas = $query->count();
        
        // Ventas Netas (Facturas - NotasCredito)
        $currentTotal = $query->selectRaw("SUM(CASE WHEN codDoc = '04' THEN -total ELSE total END) as net_total")->value('net_total');
        
        $this->totVentas  =  number_format($currentTotal,2);
        
        // Grafico Ventas por Forma de Pago (Diario) - Neto
        $this->salesByPaymentMethod_Data = Factura::join('payment_methods', 'facturas.formaPago', '=', 'payment_methods.code')
            ->select(
                \Illuminate\Support\Facades\DB::raw("payment_methods.description as method, sum(CASE WHEN facturas.codDoc = '04' THEN -facturas.total ELSE facturas.total END) as total")
            )
            ->whereBetween('facturas.created_at', [$from, $to])
            ->groupBy('payment_methods.description')
            ->get()->toArray();

         // --- ALERTS LOGIC (Admin Only or General?) ---
         // Assuming these alerts are useful for everyone or mostly Admin. 
         // For now, calculating generally.
         
         // 1. Pending Invoices (Not Authorized)
         $this->pendingInvoicesCount = Factura::whereNull('numeroAutorizacion')->orWhere('numeroAutorizacion', '')->count();

         // 2. Open Boxes (Status 1)
         $this->openBoxesCount = \App\Models\Caja::where('status', 1)->count();
         
         // 3. Low Stock 
         $this->lowStockCount = \App\Models\Product::whereRaw('stock <= minstock')->count();

         // 4. Sales Growth (Today vs Yesterday)
         $yesterday = Carbon::yesterday();
         $yesterdaySales = Factura::whereDate('created_at', $yesterday)->sum('total');
         
         if($yesterdaySales > 0) {
             $this->salesGrowth = (($currentTotal - $yesterdaySales) / $yesterdaySales) * 100;
         } else {
             $this->salesGrowth = $currentTotal > 0 ? 100 : 0;
         }
         
         // 6. Reprocesar Count
         $this->reprocesarCount = \App\Models\XmlFile::where('estado', '!=', 'autorizado')
             ->whereHas('factura', function($q) {
                 $q->whereNull('deleted_at');
             })->count();

         // 5. Signature Expiry
         $this->checkSignatureExpiry();
    }

    public function checkSignatureExpiry()
    {
        try {
            $setting = \App\Models\Setting::first();
            if ($setting && $setting->cert_file) {
                 // Try to locate the file
            if ($setting && $setting->cert_file) {
                 // Use the 'certificados' disk which is now tenant-aware
                 if (\Illuminate\Support\Facades\Storage::disk('certificados')->exists($setting->cert_file)) {
                     $path = \Illuminate\Support\Facades\Storage::disk('certificados')->path($setting->cert_file);
                     
                     $certStore = file_get_contents($path);
                     if (openssl_pkcs12_read($certStore, $certs, $setting->cert_password)) {
                         $certData = openssl_x509_parse($certs['cert']);
                         $validTo = $certData['validTo_time_t'];
                         // Calculate days remaining (positive = future, negative = expired)
                         $this->signatureDaysLeft = (int) now()->diffInDays(Carbon::createFromTimestamp($validTo), false);
                     }
                 }
            }
            }
        } catch (\Exception $e) {
            $this->signatureDaysLeft = null;
        }
    }




}
