<?php

namespace App\Http\Livewire\Admin;

use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Tenants extends Component
{
    use WithPagination;

    public $search;
    public $filter = false;
    public $plans = []; // Global Plans List

    public function getUsage($tenant)
    {
        try {
             return [
                 'used' => $tenant->getCurrentCycleInvoiceCount(),
                 'limit' => $tenant->getInvoiceLimit()
             ];
        } catch (\Exception $e) {
             return ['used' => 0, 'limit' => 0, 'error' => true];
        }
    }
    
    // Modal State
    public $showHistoryModal = false;
    public $selectedTenant = null;
    public $paymentHistory = [];

    // Manual Payment Form
    public $newPaymentAmount = '';
    public $newPaymentReference = '';
    public $newPaymentDate = '';

    // We don't need #[Layout] if we use the standard layout or define it in render
    // For now, let's assume standard 'layouts.app' or 'layouts.guest' depending on auth
    // Since this is central admin, it uses 'layouts.app' (global_asset version).

    public function render()
    {
        // Calculate Financial Stats
        $totalCollectedCents = \App\Models\PaymentHistory::sum('amount');
        $projectedMrr = Tenant::where('status', 1)->sum('amount'); // Tenants amount is in dollars

        $this->plans = \App\Models\Plan::all();

        return view('livewire.admin.tenants', [
            'tenants' => $this->loadTenants(),
            'records' => Tenant::count(),
            'month_revenue' => $this->getMonthRevenue(), // This was the old graph/list logic, kept for compatibility if needed
            'total_collected' => $totalCollectedCents / 100,
            'projected_mrr' => $projectedMrr
        ])->layout('layouts.theme.app', ['title' => 'Admin Tenants - Facta SaaS']);
    }

    function loadTenants()
    {
        $tenants = Tenant::with('latestPayment')
            ->addSelect([
                'tenants.*',
                DB::raw("
            IFNULL(
                CASE tenants.suscription_type
                    WHEN 'mensual' THEN DATE_ADD(latest_payment.created_at, INTERVAL 1 MONTH)
                    WHEN 'anual' THEN DATE_ADD(latest_payment.created_at, INTERVAL 1 YEAR)
                    ELSE tenants.bill_date
                END,
                tenants.bill_date
            ) as next_payment_due
        "),
                'latest_payment.created_at as last_payment_date'
            ])
            ->leftJoin('payment_histories as latest_payment', function ($join) {
                $join->on('tenants.id', 'latest_payment.tenant_id')
                    ->whereRaw('latest_payment.created_at = (SELECT MAX(ph.created_at) FROM payment_histories ph WHERE ph.tenant_id = tenants.id)');
            })
            ->when($this->filter, function ($query) {
                $query->whereNotNull('latest_payment.created_at');
            })
            ->when($this->search, function ($query) {
                $query->where('tenants.name', 'LIKE', '%' . $this->search . '%')
                      ->orWhere('tenants.id', 'LIKE', '%' . $this->search . '%');
            })
            ->orderBy('next_payment_due', 'ASC')
            ->paginate(10); // Reduced pagination for better fit

        return $tenants;
    }

    function getMonthRevenue()
    {
        return Tenant::leftJoin('payment_histories as latest_payment', function ($join) {
                $join->on('tenants.id', 'latest_payment.tenant_id')
                    ->whereRaw('latest_payment.created_at = (SELECT MAX(ph.created_at) FROM payment_histories ph WHERE ph.tenant_id = tenants.id)');
            })
            ->addSelect([
                'tenants.amount',
                'latest_payment.created_at as last_payment_date',
                DB::raw("
                    IFNULL(
                        CASE tenants.suscription_type
                            WHEN 'mensual' THEN DATE_ADD(latest_payment.created_at, INTERVAL 1 MONTH)
                            WHEN 'anual' THEN DATE_ADD(latest_payment.created_at, INTERVAL 1 YEAR)
                            ELSE tenants.bill_date
                        END,
                        tenants.bill_date
                    ) as next_payment_due
                ")
            ])
            // Logic: Include revenue if payment is due this month AND/OR if they paid? 
            // The original logic seemed to calculate POTENTIAL revenue or realized?
            // "MONTH(next_payment_due) <= ? ... OR last_payment_date IS NULL" implies potential due.
            // Let's stick to the reference logic exactly for now.
            ->havingRaw("(MONTH(next_payment_due) <= ? AND YEAR(next_payment_due) = ?) OR last_payment_date IS NULL", [now()->month, now()->year])
            ->when($this->search, function ($query) {
                $query->where('tenants.name', 'LIKE', '%' . $this->search . '%');
            })
            ->when($this->filter, function ($query) {
                $query->whereNotNull('latest_payment.created_at');
            })
            ->sum('amount');
    }

    public function storeManualPayment()
    {
        $this->validate([
            'newPaymentAmount' => 'required|numeric|min:0',
            'newPaymentDate' => 'required|date',
            'newPaymentReference' => 'nullable|string',
        ]);

        if ($this->selectedTenant) {
            \App\Models\PaymentHistory::create([
                'tenant_id' => $this->selectedTenant->id,
                'amount' => $this->newPaymentAmount * 100, // Store in cents
                'stripe_id' => $this->newPaymentReference ?? 'MANUAL-' . time(),
                'payment_data' => ['gateway' => 'manual', 'user_id' => auth()->id()],
                'created_at' => \Carbon\Carbon::parse($this->newPaymentDate),
                'updated_at' => \Carbon\Carbon::parse($this->newPaymentDate),
            ]);

            // Refresh List
            $this->paymentHistory = \App\Models\PaymentHistory::where('tenant_id', $this->selectedTenant->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Clear inputs
            $this->newPaymentAmount = '';
            $this->newPaymentReference = '';
            $this->newPaymentDate = '';
        }
    }

    public function viewHistory($tenantId)
    {
        $this->selectedTenant = Tenant::find($tenantId);
        if ($this->selectedTenant) {
            $this->paymentHistory = \App\Models\PaymentHistory::where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->get();
            $this->showHistoryModal = true;
        }
    }

    public function closeHistory()
    {
        $this->showHistoryModal = false;
        $this->selectedTenant = null;
        $this->paymentHistory = [];
    }

    public function setStatus($tenantId)
    {
        // Tenant ID is a string/uuid in Stancl/Tenancy
        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            $tenant->status = $tenant->status === 1 ? 0 : 1;
            $tenant->save();
        }
    }

    // Offboarding Logic
    public $showOffboardingModal = false;
    public $backupUrl = null;
    public $isBackingUp = false;
    public $offboardingStep = 1; // 1: Confirm, 2: Backing up/Done, 3: Deleted

    public function prepareOffboarding($tenantId)
    {
        $this->selectedTenant = Tenant::find($tenantId);
        $this->showOffboardingModal = true;
        $this->offboardingStep = 1;
        $this->backupUrl = null;
        $this->isBackingUp = false;
    }

    public function startOffboardingBackup()
    {
        if (!$this->selectedTenant) return;

        $this->isBackingUp = true;
        
        try {
            $service = new \App\Services\TenantOffboardingService();
            $relativePath = $service->backup($this->selectedTenant);
            
            // Generate full download URL. 
            // Note: Storage::download requires the path relative to the disk root.
            // Our backup is in 'storage/app/backups/'. 
            // We need a route to download it or use a public disk.
            // For security, backups shouldn't be public.
            // We'll create a temporary signed route or just return the path for a custom download controller.
            // For MVP simplicity: We will assume we can download via a Livewire download method or return a route.
            
            $this->backupUrl = $relativePath; 
            $this->offboardingStep = 2; // Backup Done
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error generando respaldo: ' . $e->getMessage());
        } finally {
            $this->isBackingUp = false;
        }
    }
    
    public function downloadBackup()
    {
        if ($this->backupUrl) {
             $this->dispatchBrowserEvent('download-backup', [
                 'url' => route('tenants.download', ['path' => $this->backupUrl])
             ]);
        }
    }

    public function confirmTenantDeletion()
    {
        if (!$this->selectedTenant) return;

        try {
            $service = new \App\Services\TenantOffboardingService();
            $service->delete($this->selectedTenant);
            
            $this->selectedTenant = null;
            $this->showOffboardingModal = false;
            $this->offboardingStep = 1;
            session()->flash('success', 'Tenant eliminado correctamente.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error eliminando tenant: ' . $e->getMessage());
        }
    }

    public function cancelOffboarding()
    {
        $this->showOffboardingModal = false;
        $this->selectedTenant = null;
        $this->backupUrl = null;
    }
    // -- CRUD & MANAGE TENANTS --
    public $editModalOpen = false;
    public $createModalOpen = false;
    
    // Model Properties
    public $editingTenantId = null;
    public $t_name, $t_subdomain, $t_email, $t_plan, $t_amount, $t_password, $t_bill_date;

    public function openCreateModal()
    {
        $this->resetInputFields();
        // Load plans if not loaded? We can load in render or mount.
        $this->createModalOpen = true;
    }

    public function createTenant()
    {
        $this->validate([
            't_name' => 'required|string|min:3',
            't_subdomain' => 'required|alpha_dash|unique:tenants,id',
            't_email' => 'required|email',
            't_password' => 'required|min:8',
            't_plan' => 'required',
            't_amount' => 'required|numeric'
        ]);

        // REMOVED DB::beginTransaction due to conflict with Tenancy Database Creation (DDL)
        
        $tenant = null;

        try {
            \Illuminate\Support\Facades\Log::info('Step 1: Starting Tenant Creation for ' . $this->t_subdomain);

            // 1. Create Tenant (This triggers Database Creation via Events)
            $tenant = Tenant::create([
                'id' => strtolower($this->t_subdomain),
                'name' => $this->t_name,
                'suscription_type' => $this->t_plan,
                'amount' => $this->t_amount,
                'bill_date' => \Carbon\Carbon::now()->addMonth(),
                'status' => 1
            ]);
            \Illuminate\Support\Facades\Log::info('Step 2: Tenant Model Created. ID: ' . $tenant->id);

            // 2. Create Domain
            $host = request()->getHost();
            $domain = $tenant->id . '.' . $host;
            $tenant->domains()->create(['domain' => $domain]);
            \Illuminate\Support\Facades\Log::info('Step 3: Domain Created: ' . $domain);

            // 3. Initialize Admin User
            $tenant->run(function () {
                $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
                
                $user = \App\Models\User::create([
                    'name' => $this->t_name,
                    'email' => $this->t_email,
                    'password' => \Illuminate\Support\Facades\Hash::make($this->t_password),
                    'ci' => '9999999999',
                    'phone' => '0999999999'
                ]);
                
                $user->assignRole($adminRole);
            });
            \Illuminate\Support\Facades\Log::info('Step 4: Admin User Created inside Tenant Context');

            session()->flash('message', 'Tenant creado correctamente: ' . $domain);
            $this->createModalOpen = false;
            $this->resetInputFields();

            $protocol = request()->isSecure() ? 'https://' : 'http://';
            $url = $protocol . $domain . '/login';
            
            $this->emit('tenant-created', $url);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Tenant Creation Failed: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            // Manual Rollback if Tenant was created but subsequent steps failed
            if ($tenant) {
                try {
                    $tenant->delete();
                    \Illuminate\Support\Facades\Log::info('Rollback: Cleaned up zombie tenant.');
                } catch (\Exception $ex) {
                    \Illuminate\Support\Facades\Log::error('Rollback Failed: ' . $ex->getMessage());
                }
            }
            
            session()->flash('error', 'Error creando tenant: ' . $e->getMessage());
        }
    }

    public function editTenant($id)
    {
        $t = Tenant::find($id);
        if($t) {
            $this->editingTenantId = $id;
            $this->t_name = $t->name;
            $this->t_subdomain = $t->id; // Read only
            $this->t_plan = $t->suscription_type;
            $this->t_amount = $t->amount;
            $this->t_bill_date = \Carbon\Carbon::parse($t->bill_date)->format('Y-m-d');
            
            $this->editModalOpen = true;
        }
    }

    public function updateTenant()
    {
        $this->validate([
            't_name' => 'required|string',
            't_plan' => 'required',
            't_amount' => 'required|numeric',
            't_bill_date' => 'required|date'
        ]);

        if($this->editingTenantId) {
            $t = Tenant::find($this->editingTenantId);
            $t->update([
                'name' => $this->t_name,
                'suscription_type' => $this->t_plan,
                'amount' => $this->t_amount,
                'bill_date' => $this->t_bill_date
            ]);
            
            session()->flash('message', 'Tenant actualizado correctamente.');
            $this->editModalOpen = false;
            $this->resetInputFields();
        }
    }

    public function closeModals()
    {
        $this->createModalOpen = false;
        $this->editModalOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->t_name = '';
        $this->t_subdomain = '';
        $this->t_email = '';
        $this->t_password = '';
        $this->t_plan = '';
        $this->t_amount = '';
        $this->t_bill_date = '';
        $this->editingTenantId = null;
    }
    public function backupTenant($id)
    {
        try {
            $service = app(\App\Services\TenantOffboardingService::class);
            $tenant = Tenant::findOrFail($id);
            
            $zipPath = $service->backup($tenant);
            
            $this->dispatchBrowserEvent('notify-custom', [
                'type' => 'success', 
                'message' => 'Respaldo generado. Iniciando descarga...'
            ]);
            
            $this->dispatchBrowserEvent('download-backup', [
                'url' => route('tenants.download', ['path' => $zipPath])
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Backup Download Fail: " . $e->getMessage());
            $this->dispatchBrowserEvent('notify-custom', [
                'type' => 'error', 
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
