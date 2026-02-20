<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Models\Setting;

try {
    echo "Current Mail Driver: " . Config::get('mail.default') . "\n";
    echo "Host: " . Config::get('mail.mailers.smtp.host') . "\n";
    echo "Port: " . Config::get('mail.mailers.smtp.port') . "\n";
    echo "Username: " . Config::get('mail.mailers.smtp.username') . "\n";
    echo "From Address: " . Config::get('mail.from.address') . "\n";
    echo "From Name: " . Config::get('mail.from.name') . "\n";

    $setting = Setting::first();
    if ($setting) {
        echo "Tenant Email: " . $setting->email . "\n";
    } else {
        echo "No Setting found.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
