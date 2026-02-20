<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use App\Models\Tenant;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Placeholder for VerifyEmail if it doesn't exist yet, avoiding verify error.
// If User wants it, we'll create the Mailable later.
// use App\Mail\VerifyEmail; 

class Payator extends Component
{
    public $plans;
    public $selectedPlan;
    public $name; // Admin User Name
    public $email;
    public $password;
    public $password_confirmation;
    public $company_name; // Mapped to storename/name in Tenant
    public $storename; // Legacy variable from facta, we'll use consistent naming or map it.
    public $tenant_id; // Subdomain
    public $amount;
    public $description;
    public $ci;
    public $phone;
    public $paymentUrl = null;

    protected $rules = [
        'company_name' => 'required|string|min:3|max:255',
        'tenant_id' => 'required|string|alpha_dash|min:3|max:50|unique:tenants,id',
        'email' => 'required|email|max:255',
        'password' => 'required|string|min:8|confirmed',
        'ci' => 'required|string|min:10|max:13',
        'phone' => 'nullable|string|max:15',
    ];

    public function mount()
    {
        $this->plans = Plan::orderBy('price', 'asc')->get();
        // Default to first plan or logic to select
        if($this->plans->count() > 0) {
            $this->selectPlan($this->plans->first()->id);
        }
    }

    public function updatedTenantId($value)
    {
        $this->tenant_id = Str::slug($value);
    }

    public function selectPlan($planId)
    {
        $plan = $this->plans->find($planId);
        if ($plan) {
            $this->selectedPlan = $plan;
            $this->amount = $plan->price;
            $this->description = "Suscripción al plan " . $plan->name;
            $this->dispatchBrowserEvent('open-modal');
        }
    }

    public function register()
    {
        $this->validate();
        
        Log::info('Register Attempt', [
            'plan' => $this->selectedPlan->name ?? 'None',
            'amount' => $this->amount,
            'tenant' => $this->tenant_id
        ]);

        try {
            // 1. Create Tenant (Pending Payment/Activation)
            // 'status' => 0 (Inactive) until payment is confirmed
            $tenant = Tenant::create([
                'id' => $this->tenant_id,
                'name' => $this->company_name,
                'suscription_type' => $this->selectedPlan->name ?? 'Demo',
                'amount' => $this->amount ?? 0,
                'bill_date' => Carbon::now()->addDays($this->daysToAdd()),
                'status' => 0 // Inactive
            ]);
            
            // 2. Create Domain (Dynamic Host)
            $host = request()->getHost();
            $domain = $this->tenant_id . '.' . $host; 
            $tenant->domains()->create(['domain' => $domain]);

            // 3. PERSIST DATA FOR LATER (Post-Payment Setup)
            // We store critical registration data to create the admin user ONLY after payment.
            $tenant->update([
                'pending_data' => [
                    'admin_email' => $this->email,
                    'admin_password' => $this->password, // Will be hashed during actual user creation
                    'admin_ci' => $this->ci,
                    'admin_phone' => $this->phone,
                    'company_name' => $this->company_name,
                    'domain' => $domain
                ]
            ]);

            // 4. Process Payment (SETUP DEFERRED until PaymentController@response)
            if ($this->amount > 0) {
                try {
                    $paymentService = app(\App\Services\Payments\PaymentGatewayInterface::class);
                    // Ensure clientTransactionId is max 50 chars (Payphone Limit)
                    // tenant_id can be up to 50 chars. We need to append unique suffix.
                    // Strategy: Truncate tenant_id to 35 chars, append 10 digit timestamp + 3 chars separator = 48 chars.
                    $shortTenant = substr($this->tenant_id, 0, 35);
                    $reference = $shortTenant . '---' . time();
                    
                    $response = $paymentService->preparePayment(
                        $this->amount, 
                        $reference,
                        [
                            'email' => $this->email, 
                            'phone' => $this->phone ?? '0999999999', 
                            'ci' => $this->ci, 
                            'name' => $this->company_name
                        ]
                    );
    
                if ($response['success']) {
                    Log::info('Payment URL Generated', ['url' => $response['url']]);
                    $this->paymentUrl = $response['url']; // Set manual link
                    $this->dispatchBrowserEvent('init-payment', ['url' => $response['url']]);
                    return;
                } else {
                        // CLEANUP: Delete tenant and database if payment preparation fails
                        $tenant->delete(); 
                        $this->addError('tenant_id', 'Error iniciando pago: ' . $response['message']);
                        return;
                    }
                } catch (\Exception $e) {
                     // CLEANUP: Delete tenant and database if payment gateway crashes
                     $tenant->delete();
                     $this->addError('tenant_id', 'Error de Conexión Pago: ' . $e->getMessage());
                     return;
                }
            } else {
                // Free Plan - Activate and Setup Immediately
                Log::info('Free Plan detected. Running setup immediately: ' . $this->tenant_id);
                $setupService = new \App\Services\TenantSetupService();
                $pending = $tenant->pending_data;
                $setupService->finalize($tenant, $pending);
                
                return redirect()->to('http://' . $domain . '/login');
            }

        } catch (\Exception $e) {
            $this->addError('tenant_id', 'Error: ' . $e->getMessage());
        }
    }

    function daysToAdd()
    {
        if (empty($this->selectedPlan)) return 0;
        $planName = strtolower(trim($this->selectedPlan->name));

        $daysMapping = [
            'mensual' => 30,
            'anual' => 365,
            'lifetime' => 3650,
        ];

        return $daysMapping[$planName] ?? 0;
    }

    public function render()
    {
        return view('livewire.payator');
    }
}
