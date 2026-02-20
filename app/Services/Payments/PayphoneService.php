<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayphoneService implements PaymentGatewayInterface
{
    protected $baseUrl;
    protected $token;
    protected $isSandbox;

    public function __construct()
    {
        $this->isSandbox = config('services.payphone.mode') === 'sandbox';
        
        $this->baseUrl = 'https://pay.payphonetodoesposible.com/api';
            
        $this->token = config('services.payphone.token');
    }

    public function preparePayment(float $amount, string $reference, array $clientData): array
    {
        // Sandbox bypass for testing without real API call if needed
        // But the requirements asked to use the Real Sandbox.
        // We will implement the real API call.

        $amountInCents = (int) ($amount * 100);
        // Payphone requires tax separate. Assiming $amount includes tax for now or 0 tax for SaaS.
        // Let's assume 0 tax for simplicity or calculate if needed.
        $tax = 0; 
        $amountWithTax = 0;
        $amountWithoutTax = $amountInCents; 
        
        // Sanitize Phone: Keep only digits
        $cleanPhone = preg_replace('/[^0-9]/', '', $clientData['phone'] ?? '');
        
        // Formateo para Payphone Ecuador: Debe empezar con 593 y tener 12 dígitos
        // Si empieza con 0 y tiene 10 dígitos (ej: 0987505655 -> 593987505655)
        if (str_starts_with($cleanPhone, '0') && strlen($cleanPhone) === 10) {
            $cleanPhone = '593' . substr($cleanPhone, 1);
        }
        
        // Si tiene 9 dígitos (ej: 987505655 -> 593987505655)
        if (!str_starts_with($cleanPhone, '593') && strlen($cleanPhone) === 9) {
            $cleanPhone = '593' . $cleanPhone;
        }

        // Validación final de seguridad
        if (strlen($cleanPhone) < 10) {
             $cleanPhone = '593999999999';
        }
        
        Log::info('Payphone Payload Prep', [
            'original_phone' => $clientData['phone'] ?? 'null',
            'sent_phone' => $cleanPhone
        ]);

        $payload = [
            'amount' => $amountInCents,
            'amountWithoutTax' => $amountWithoutTax,
            'amountWithTax' => 0,
            'tax' => 0,
            'clientTransactionId' => $reference,
            'currency' => 'USD',
            'email' => $clientData['email'],
            'responseUrl' => $this->isSandbox ? route('payment.response') : 'https://facta.ec/payment/response',
            'cancellationUrl' => $this->isSandbox ? route('payment.cancel') : 'https://facta.ec/payment/cancel',
        ];

        Log::info('[PAYPHONE_REQUEST] Payload:', $payload);

        try {
            $response = Http::withToken($this->token)
                ->post("{$this->baseUrl}/button/Prepare", $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('[PAYPHONE_RESPONSE] Success Body:', ['full_response' => $response->body()]);
                
                // Try to get a valid payment URL
                $url = $data['payWithCard'] ?? ($data['payWithPayPhone'] ?? null);

                return [
                    'success' => true,
                    'paymentId' => $data['paymentId'],
                    'url' => $url, 
                    'message' => 'Payment prepared successfully'
                ];
            } else {
                Log::error('[PAYPHONE_RESPONSE] Error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [
                    'success' => false,
                    'message' => 'Error communicating with Payphone: ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Payphone Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    public function confirmPayment(string $id, string $clientTxId): array
    {
        try {
            $response = Http::withToken($this->token)
                ->post("{$this->baseUrl}/button/Confirm", [
                    'id' => $id,
                    'clientTxId' => $clientTxId
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Payphone Confirm Success', ['data' => $data]);
                return [
                    'success' => true,
                    'data' => $data // Contains clientName, phoneNumber, etc.
                ];
            } else {
                Log::error('Payphone Confirm Error', ['response' => $response->body()]);
                return [
                    'success' => false,
                    'message' => 'Error confirming payment: ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Payphone Confirm Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
}
