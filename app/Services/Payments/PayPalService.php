<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService implements PaymentGatewayInterface
{
    protected string $mode;
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected string $currency;
    protected string $baseUrl;

    public function __construct()
    {
        $this->mode = (string) config('services.paypal.mode', 'simulation');
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->currency = (string) config('services.paypal.currency', 'USD');
        $this->baseUrl = rtrim((string) config('services.paypal.base_url', 'https://api-m.sandbox.paypal.com'), '/');
    }

    public function isSimulation(): bool
    {
        return $this->mode === 'simulation'
            || empty($this->clientId)
            || empty($this->clientSecret);
    }

    public function preparePayment(float $amount, string $reference, array $clientData): array
    {
        if ($this->isSimulation()) {
            return [
                'success' => true,
                'paymentId' => 'SIM-' . $reference,
                'url' => route('payment.paypal.simulate', [
                    'domain' => request()->getHost(),
                    'reference' => $reference,
                    'amount' => number_format($amount, 2, '.', ''),
                ]),
                'message' => 'Pago simulado preparado',
            ];
        }

        try {
            $token = $this->accessToken();
            if (!$token) {
                return ['success' => false, 'message' => 'No se pudo autenticar con PayPal.'];
            }

            $response = Http::withToken($token)
                ->acceptJson()
                ->post($this->baseUrl . '/v2/checkout/orders', [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $reference,
                        'custom_id' => $reference,
                        'description' => 'Suscripción Adlatere SaaS',
                        'amount' => [
                            'currency_code' => $this->currency,
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                    ]],
                    'application_context' => [
                        'brand_name' => 'Adlatere SaaS',
                        'user_action' => 'PAY_NOW',
                        'return_url' => route('payment.paypal.return', ['domain' => request()->getHost(), 'reference' => $reference]),
                        'cancel_url' => route('payment.paypal.cancel', ['domain' => request()->getHost(), 'reference' => $reference]),
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('[PAYPAL_ORDER_ERROR]', ['body' => $response->body()]);
                return ['success' => false, 'message' => 'PayPal no pudo crear la orden.'];
            }

            $order = $response->json();
            $approveUrl = collect($order['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

            if (!$approveUrl) {
                return ['success' => false, 'message' => 'PayPal no devolvió el enlace de pago.'];
            }

            return [
                'success' => true,
                'paymentId' => $order['id'] ?? null,
                'url' => $approveUrl,
                'message' => 'Orden PayPal creada',
            ];
        } catch (\Throwable $e) {
            Log::error('[PAYPAL_PREPARE_EXCEPTION]', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Error preparando el pago con PayPal: ' . $e->getMessage()];
        }
    }

    public function captureOrder(string $orderId): array
    {
        if ($this->isSimulation()) {
            return [
                'success' => true,
                'data' => ['id' => $orderId, 'status' => 'COMPLETED', 'simulated' => true],
            ];
        }

        try {
            $token = $this->accessToken();
            if (!$token) {
                return ['success' => false, 'message' => 'No se pudo autenticar con PayPal.'];
            }

            $response = Http::withToken($token)
                ->acceptJson()
                ->withHeaders(['PayPal-Request-Id' => $orderId])
                ->post($this->baseUrl . '/v2/checkout/orders/' . urlencode($orderId) . '/capture');

            if (!$response->successful()) {
                Log::error('[PAYPAL_CAPTURE_ERROR]', ['body' => $response->body()]);
                return ['success' => false, 'message' => 'PayPal no pudo confirmar el pago.'];
            }

            $data = $response->json();
            if (($data['status'] ?? null) !== 'COMPLETED') {
                return ['success' => false, 'message' => 'El pago no está completado en PayPal.'];
            }

            return ['success' => true, 'data' => $data];
        } catch (\Throwable $e) {
            Log::error('[PAYPAL_CAPTURE_EXCEPTION]', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Error confirmando el pago con PayPal: ' . $e->getMessage()];
        }
    }

    public function accessToken(): ?string
    {
        try {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post($this->baseUrl . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('[PAYPAL_TOKEN_ERROR]', ['body' => $response->body()]);
        } catch (\Throwable $e) {
            Log::error('[PAYPAL_TOKEN_EXCEPTION]', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
