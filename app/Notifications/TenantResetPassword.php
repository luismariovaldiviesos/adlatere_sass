<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantResetPassword extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        if (env('SENDGRID_API_KEY')) {
            $this->sendViaSendGrid($notifiable);
            return []; // Handled manually
        }
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // Attempt to get Tenant Name for "From"
        $fromName = config('app.name');
        if (function_exists('empresa')) {
            $company = empresa(); 
            if ($company) $fromName = $company->nombreComercial;
        }

        return (new MailMessage)
            ->subject('Restablecer Contraseña - ' . $fromName)
            ->line('Recibiste este correo porque solicitaste restablecer tu contraseña.')
            ->action('Restablecer Contraseña', $url)
            ->line('Si no solicitaste este cambio, omite este mensaje.');
    }

    protected function sendViaSendGrid($notifiable)
    {
        try {
            $apiKey = env('SENDGRID_API_KEY');
            $client = new \GuzzleHttp\Client();
            
            // Construct URL
            $url = url(route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $fromName = 'Facta SaaS';
            if (function_exists('empresa')) {
                $company = empresa();
                if ($company) $fromName = $company->nombreComercial;
            }
            $fromEmail = env('MAIL_FROM_ADDRESS', 'noreply@facta.ec');

            $htmlContent = "<p>Hola,</p>";
            $htmlContent .= "<p>Recibiste este correo porque solicitaste restablecer tu contraseña para <strong>$fromName</strong>.</p>";
            $htmlContent .= "<p style='text-align:center; margin: 20px 0;'><a href='$url' style='background-color:#4CAF50; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Restablecer Contraseña</a></p>";
            $htmlContent .= "<p>Si el botón no funciona, copia y pega este enlace:</p>";
            $htmlContent .= "<p><small>$url</small></p>";
            $htmlContent .= "<p>Si no solicitaste este cambio, omite este mensaje.</p>";

            $payload = [
                'personalizations' => [
                    [
                        'to' => [['email' => $notifiable->email]],
                        'subject' => 'Restablecer Contraseña - ' . $fromName
                    ]
                ],
                'from' => ['email' => $fromEmail, 'name' => $fromName],
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $htmlContent
                    ]
                ]
            ];

            $client->post('https://api.sendgrid.com/v3/mail/send', [
                'headers' => [
                    'Authorization' => "Bearer $apiKey",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            \Illuminate\Support\Facades\Log::info('[RESET_PASSWORD] Email sent via SendGrid API to ' . $notifiable->email);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[RESET_PASSWORD] Failed to send via API: ' . $e->getMessage());
        }
    }
}
