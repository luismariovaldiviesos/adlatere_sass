<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function response(Request $request)
    {
        // Payphone redirects here with ?id={paymentId}&clientTransactionId={clientTransactionId}
        $paymentId = $request->query('id');
        $clientTxId = $request->query('clientTransactionId');

        Log::info('[PAYPHONE_RESPONSE] Incoming:', [
            'id' => $paymentId,
            'clientTransactionId' => $clientTxId
        ]);
        
        // Handle "tenant_id---timestamp" format to ensure uniqueness in Payphone
        $start_delimiter = strpos($clientTxId, '---');
        if ($start_delimiter !== false) {
             $tenantId = substr($clientTxId, 0, $start_delimiter);
        } else {
             $tenantId = $clientTxId;
        }

        if (!$paymentId || !$clientTxId) {
            // Check for specific error message from Payphone
            $errorMsg = $request->query('msg') ?? $request->query('message');
            if ($errorMsg) {
                return redirect('/')->with('error', 'Payphone Error: ' . urldecode($errorMsg));
            }
            return redirect('/')->with('error', 'Respuesta de pago inválida.');
        }

        // Ideally call Payphone "Confirm" API here to verify status.
        // For MVP/Sandbox: assume success if ID exists, or rely on Payphone status.
        // Let's activate the tenant.

        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            // 1. LOG PAYMENT RECORD (PRIORITY)
            // We do this first so the Admin sees the transaction even if setup fails.
            try {
                $existingPayment = PaymentHistory::where('stripe_id', $paymentId)->first();
                if (!$existingPayment) {
                    Log::info('[PAYMENT_SUCCESS] Logging payment history record: ' . $paymentId);
                    $payphoneService = new \App\Services\Payments\PayphoneService();
                    $verification = $payphoneService->confirmPayment($paymentId, $clientTxId);
                    $payphoneData = $verification['success'] ? $verification['data'] : [];

                    PaymentHistory::create([
                        'tenant_id' => $tenant->id,
                        'amount' => $tenant->amount ? $tenant->amount * 100 : 0, 
                        'stripe_id' => $paymentId,
                        'payment_data' => array_merge([
                            'clientTransactionId' => $clientTxId,
                            'gateway' => 'payphone'
                        ], $payphoneData)
                    ]);
                    Log::info('[PAYMENT_SUCCESS] Payment History Logged Successfully');
                }
            } catch (\Exception $e) {
                Log::error('[PAYMENT_SUCCESS] Could not log payment history: ' . $e->getMessage());
            }

            // 2. Handle RENEWAL (Status 1)
            if ($tenant->status == 1) {
                Log::info('[PAYMENT_SUCCESS] Handling RENEWAL for active tenant: ' . $tenant->id);
                
                // Update bill_date: add 1 month (30 days)
                $currentBillDate = $tenant->bill_date ? \Carbon\Carbon::parse($tenant->bill_date) : now();
                $newBillDate = $currentBillDate->gt(now()) ? $currentBillDate->addMonth() : now()->addMonth();
                
                $tenant->update([
                    'bill_date' => $newBillDate,
                    'last_payment_date' => now(),
                    'next_payment_due' => $newBillDate
                ]);

                Log::info('[PAYMENT_SUCCESS] Renewal Complete. New bill date: ' . $newBillDate);
                $domain = $tenant->domains->first()->domain;
                return redirect('http://' . $domain . '/dash')->with('success', 'Tu suscripción ha sido renovada exitosamente hasta el ' . $newBillDate->format('d/m/Y'));
            }

            // 3. Handle NEW REGISTRATION (Status 0)
            $pending = $tenant->pending_data;
            if (is_string($pending)) {
                $pending = json_decode($pending, true);
            }

            if ($pending && is_array($pending)) {
                $setupService = new \App\Services\TenantSetupService();
                $success = $setupService->finalize($tenant, $pending);
                
                if (!$success) {
                    return redirect('/')->with('error', 'Pago procesado, pero hubo un error configurando tu sistema. Contacta a soporte.');
                }
            } else {
                Log::warning('[PAYMENT_SUCCESS] No pending data found for registration tenant: ' . $tenantId);
                $tenant->update(['status' => 1]);
            }
            
            $domain = $tenant->domains->first()->domain ?? '/';
            Log::info('[PAYMENT_SUCCESS] Registration Process Finished. Redirecting user.');
            return redirect('http://' . $domain . '/login')->with('success', 'Pago exitoso. Bienvenido!');
        }

        return redirect('/')->with('error', 'Tenant no encontrado.');
    }

    public function cancel(Request $request)
    {
        return redirect('/')->with('error', 'El proceso de pago fue cancelado.');
    }

    /**
     * Genera un link de pago de Payphone para renovar una suscripción existente.
     */
    public function createRenewal($tenantId)
    {
        Log::info('[RENEWAL_START] Attempt for tenant: ' . $tenantId);
        $tenant = Tenant::findOrFail($tenantId);
        
        // Determinar monto según el plan (Búsqueda robusta)
        $plan = \App\Models\Plan::where('name', $tenant->suscription_type)->first();
        if (!$plan) {
            $plan = \App\Models\Plan::where('name', 'LIKE', $tenant->suscription_type)->first();
        }
        
        $amount = $plan ? $plan->price : 0;

        if ($amount <= 0) {
            Log::warning('[RENEWAL_ERROR] Plan price not found or 0', [
                'tenant' => $tenant->id,
                'plan' => $tenant->suscription_type
            ]);
            return back()->with('error', "No se pudo determinar el precio para el plan: {$tenant->suscription_type}. Por favor, contacta a soporte.");
        }

        $paymentGateway = app(\App\Services\Payments\PaymentGatewayInterface::class);
        
        // Identificador de transacción único: tenantId + timestamp
        $clientTxId = $tenant->id . '---' . time();

        $pending = $tenant->pending_data;
        $clientData = [
            'email' => $pending['admin_email'] ?? ($tenant->email ?? 'pago@adlatere.ec'),
            'phone' => $pending['admin_phone'] ?? '0999999999',
            'ci' => $pending['admin_ci'] ?? '9999999999',
            'name' => $tenant->name
        ];

        $response = $paymentGateway->preparePayment($amount, $clientTxId, $clientData);

        if ($response['success']) {
            return view('payments.redirect', ['url' => $response['url']]);
        }

        return back()->with('error', 'Error del pasarela de pago: ' . ($response['message'] ?? 'Desconocido'));
    }

    public function paypalSimulate(Request $request)
    {
        $reference = (string) $request->query('reference');
        $amount = (float) $request->query('amount', 0);
        $tenantId = $this->tenantIdFromReference($reference);
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if (!$tenant) {
            return redirect('/')->with('error', 'No se encontró el despacho para simular el pago.');
        }

        return view('payments.paypal-simulate', [
            'tenant' => $tenant,
            'reference' => $reference,
            'amount' => $amount,
        ]);
    }

    public function paypalSimulateApprove(Request $request)
    {
        $reference = (string) $request->input('reference');
        $tenantId = $this->tenantIdFromReference($reference);
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if (!$tenant) {
            return redirect('/')->with('error', 'No se encontró el despacho para simular el pago.');
        }

        $orderId = 'SIM-' . strtoupper(substr(hash('sha256', $reference . microtime(true)), 0, 18));

        return redirect()->route('payment.paypal.return', [
            'domain' => request()->getHost(),
            'reference' => $reference,
            'token' => $orderId,
            'simulated' => 1,
        ]);
    }

    public function paypalReturn(Request $request)
    {
        $reference = (string) $request->query('reference');
        $orderId = (string) ($request->query('token') ?: $request->query('order'));
        $tenantId = $this->tenantIdFromReference($reference);
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if (!$tenant) {
            return redirect('/')->with('error', 'No se encontró el despacho asociado al pago.');
        }

        if (!$orderId) {
            return redirect('/')->with('error', 'PayPal no devolvió el identificador de la orden.');
        }

        $paypal = new \App\Services\Payments\PayPalService();
        $capture = $paypal->captureOrder($orderId);

        if (!$capture['success']) {
            Log::error('[PAYPAL_RETURN_ERROR]', [
                'tenant' => $tenant->id,
                'order' => $orderId,
                'message' => $capture['message'] ?? 'Error desconocido',
            ]);

            return redirect('/')->with('error', $capture['message'] ?? 'No se pudo confirmar el pago con PayPal.');
        }

        return $this->completeTenantPayment($tenant, 'paypal', $orderId, [
            'gateway' => 'paypal',
            'reference' => $reference,
            'capture' => $capture['data'] ?? [],
        ]);
    }

    public function paypalCancel(Request $request)
    {
        return redirect('/')->with('error', 'El pago con PayPal fue cancelado.');
    }

    public function registrationComplete($tenantId)
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return redirect('/')->with('error', 'No se encontró el despacho registrado.');
        }

        $domain = optional($tenant->domains->first())->domain;

        if (!$domain) {
            return redirect('/')->with('error', 'El despacho no tiene un dominio configurado.');
        }

        $hosts = app(\App\Services\LocalHostsService::class)->ensure($domain);

        if ($hosts['ok'] || !app()->environment('local')) {
            return redirect('http://' . $domain . '/login')
                ->with('success', 'Tu espacio está listo. Inicia sesión.');
        }

        $scriptUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'registration.hostsScript',
            now()->addHours(4),
            ['domain' => request()->getHost(), 'tenant' => $tenant->id]
        );

        return view('registro.tenant-listo', [
            'tenant' => $tenant,
            'domain' => $domain,
            'hostsEntry' => $hosts['entry'],
            'hostsPath' => $hosts['hosts_path'],
            'scriptUrl' => $scriptUrl,
        ]);
    }

    public function hostsScript(Request $request, $tenantId)
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            abort(404);
        }

        $domain = optional($tenant->domains->first())->domain;
        if (!$domain) {
            abort(404);
        }

        $content = app(\App\Services\LocalHostsService::class)->scriptFor($domain);
        $filename = 'hosts-' . preg_replace('/[^a-z0-9_-]/i', '-', $tenant->id) . '.bat';

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, ['Content-Type' => 'application/octet-stream']);
    }

    private function tenantIdFromReference(string $reference): ?string
    {
        if ($reference === '') {
            return null;
        }

        $delimiter = strpos($reference, '---');

        return $delimiter === false ? $reference : substr($reference, 0, $delimiter);
    }

    private function completeTenantPayment(Tenant $tenant, string $gateway, string $paymentId, array $paymentData = [])
    {
        try {
            \App\Models\PaymentHistory::firstOrCreate(
                ['stripe_id' => $paymentId],
                [
                    'tenant_id' => $tenant->id,
                    'amount' => $tenant->amount ? $tenant->amount * 100 : 0,
                    'payment_data' => $paymentData,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('[PAYMENT_HISTORY_ERROR]', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
        }

        if ((int) $tenant->status === 1) {
            $currentBillDate = $tenant->bill_date
                ? \Carbon\Carbon::parse($tenant->bill_date)
                : now();

            $newBillDate = $currentBillDate->gt(now())
                ? $currentBillDate->addMonth()
                : now()->addMonth();

            $tenant->update([
                'bill_date' => $newBillDate,
                'last_payment_date' => now(),
                'next_payment_due' => $newBillDate,
            ]);

            $domain = optional($tenant->domains->first())->domain;

            return redirect('http://' . $domain . '/dash')
                ->with('success', 'Tu suscripción ha sido renovada hasta el ' . $newBillDate->format('d/m/Y'));
        }

        $pending = $tenant->pending_data;
        if (is_string($pending)) {
            $pending = json_decode($pending, true);
        }

        if ($pending && is_array($pending)) {
            $setup = new \App\Services\TenantSetupService();
            if (!$setup->finalize($tenant, $pending)) {
                return redirect('/')->with('error', 'Pago procesado, pero no se pudo configurar el sistema. Contacta a soporte.');
            }
        } else {
            Log::warning('[PAYMENT_COMPLETE] Tenant sin pending_data: ' . $tenant->id);
            $tenant->update(['status' => 1]);
        }

        $domain = optional($tenant->domains->first())->domain;

        if (app()->environment('local') && $domain) {
            $hosts = app(\App\Services\LocalHostsService::class)->ensure($domain);
            if (!$hosts['ok']) {
                return redirect()->route('registration.complete', [
                    'domain' => request()->getHost(),
                    'tenant' => $tenant->id,
                ]);
            }
        }

        return redirect('http://' . $domain . '/login')
            ->with('success', 'Pago exitoso. Tu espacio está listo.');
    }
}
