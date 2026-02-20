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

        $payphoneService = new \App\Services\Payments\PayphoneService();
        
        // Identificador de transacción único: tenantId + timestamp
        $clientTxId = $tenant->id . '---' . time();

        $pending = $tenant->pending_data;
        $clientData = [
            'email' => $pending['admin_email'] ?? ($tenant->email ?? 'pago@facta.ec'),
            'phone' => $pending['admin_phone'] ?? '0999999999',
            'ci' => $pending['admin_ci'] ?? '9999999999',
            'name' => $tenant->name
        ];

        $response = $payphoneService->preparePayment($amount, $clientTxId, $clientData);

        if ($response['success']) {
            return view('payments.redirect', ['url' => $response['url']]);
        }

        return back()->with('error', 'Error Payphone: ' . ($response['message'] ?? 'Desconocido'));
    }
}
