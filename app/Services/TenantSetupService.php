<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Setting;
use App\Mail\WelcomeTenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;

class TenantSetupService
{
    /**
     * Realiza la configuración técnica final del inquilino.
     * 
     * @param Tenant $tenant
     * @param array $data Datos obtenidos de pending_data (email, password, etc.)
     * @return bool Success status
     */
    public function finalize(Tenant $tenant, array $data): bool
    {
        Log::info('[SETUP_SERVICE] Starting final setup for: ' . $tenant->id);

        try {
            // 1. Inicializar Tenancy (Crea DB y corre migraciones/seeders)
            tenancy()->initialize($tenant);
            Log::info('[SETUP_SERVICE] Tenancy Initialized');

            // 2. Asegurar que el Rol Admin exista
            $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);

            // 3. Crear/Actualizar Usuario Admin (Heredar ID 1 de seeder si existe)
            $user = User::first();
            $userData = [
                'name' => $data['company_name'] ?? $tenant->name,
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'ci' => $data['admin_ci'] ?? '9999999999',
                'phone' => $data['admin_phone'] ?? '0999999999',
                'profile' => 'Admin',
                'status' => 'ACTIVE',
            ];

            if ($user) {
                Log::info('[SETUP_SERVICE] Updating Seeder User ID: ' . $user->id);
                $user->update($userData);
            } else {
                Log::info('[SETUP_SERVICE] Creating New Admin User');
                $user = User::create($userData);
            }
            $user->syncRoles($adminRole);

            // 4. Inicializar Configuración de Empresa
            Log::info('[SETUP_SERVICE] Updating Settings');
            Setting::updateOrCreate(
                ['id' => 1],
                [
                    'razonSocial' => $data['company_name'],
                    'nombreComercial' => $data['company_name'],
                    'email' => $data['admin_email'],
                    'ruc' => $data['admin_ci'] ?? '9999999999',
                ]
            );

            // 5. Enviar Email de Bienvenida (Vía API para saltar bloqueos de puerto)
            $this->sendWelcomeEmail($tenant, $user, $data);

            tenancy()->end();

            // 6. Activar Inquilino
            $tenant->update([
                'status' => 1,
                'pending_data' => null,
                'last_payment_date' => now(),
                'next_payment_due' => $tenant->bill_date ?? now()->addMonth(),
            ]);

            Log::info('[SETUP_SERVICE] Setup Complete for: ' . $tenant->id);
            return true;

        } catch (\Exception $e) {
            Log::error('[SETUP_SERVICE] Setup CRASH: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Envía el correo usando la API de SendGrid si está configurada,
     * de lo contrario usa el driver de correo estándar.
     */
    private function sendWelcomeEmail($tenant, $user, $data)
    {
        try {
            $sendgridKey = env('SENDGRID_API_KEY');
            $url = 'http://' . ($data['domain'] ?? $tenant->domains->first()->domain);

            if (!empty($sendgridKey)) {
                Log::info('[SETUP_SERVICE] Dispatching Welcome Mail via SendGrid API');
                
                $client = new Client();
                $fromEmail = env('MAIL_FROM_ADDRESS', 'noreply@facta.ec');
                
                $htmlContent = view('emails.welcome_tenant', [
                    'tenant' => $tenant,
                    'user' => $user,
                    'url' => $url
                ])->render();

                $payload = [
                    'personalizations' => [
                        [
                            'to' => [['email' => $data['admin_email']]],
                            'subject' => '¡Bienvenido a Facta SaaS!'
                        ]
                    ],
                    'from' => ['email' => $fromEmail, 'name' => 'Facta SaaS'],
                    'content' => [
                        [
                            'type' => 'text/html',
                            'value' => $htmlContent
                        ]
                    ]
                ];

                $response = $client->post('https://api.sendgrid.com/v3/mail/send', [
                    'headers' => [
                        'Authorization' => "Bearer $sendgridKey",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                ]);

                if ($response->getStatusCode() == 202) {
                    Log::info('[SETUP_SERVICE] Welcome Email Sent (API) to: ' . $data['admin_email']);
                } else {
                    Log::error('[SETUP_SERVICE] SendGrid API returned status ' . $response->getStatusCode());
                }
            } else {
                Mail::to($data['admin_email'])->send(new WelcomeTenant($tenant, $user, $url));
                Log::info('[SETUP_SERVICE] Welcome Email Sent (SMTP Fallback) to: ' . $data['admin_email']);
            }
        } catch (\Exception $e) {
            Log::error('[SETUP_SERVICE] Welcome Email Delivery Failed: ' . $e->getMessage());
        }
    }
}
