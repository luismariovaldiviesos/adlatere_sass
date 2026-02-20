<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use App\Services\SriSoapService;

class SriMonitor extends Component
{
    public $status = 'loading';
    public $msg = 'Verificando SRI...';
    public $color = 'gray';

    public function render()
    {
        $this->checkStatus();
        return view('livewire.sri-monitor');
    }

    public function checkStatus()
    {
        // Cache result for 5 minutes (300 seconds) to avoid banning
        // Cache result for 5 minutes (300 seconds) unique per tenant/environment
        $tenantId = tenant('id') ?? 'central';
        $env = empresa()->ambiente ?? '1';
        $cacheKey = "sri_status_{$tenantId}_{$env}";
        
        $sriDetails = Cache::remember($cacheKey, 60, function () { // Reduced to 60s for testing
            $service = new SriSoapService();
            return $service->checkConnection();
        });

        if ($sriDetails['status'] === 'online') {
            $this->status = 'ONLINE';
            $this->color = 'green';
            $this->msg = $sriDetails['msg'];
        } else {
            $this->status = 'OFFLINE';
            $this->color = 'red';
            $this->msg = $sriDetails['msg'];
        }
        
        // Append Debug Info
        $this->msg .= " | Env: " . ($env == '1' ? 'Pruebas' : 'Producción');
    }
}
