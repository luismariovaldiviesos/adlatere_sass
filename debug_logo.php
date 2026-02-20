<?php
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$empresa = empresa();
echo "Razon Social: " . $empresa->razonSocial . "\n";
echo "Logo DB Value: " . $empresa->logo . "\n";

$path = storage_path('app/public/logo/' . $empresa->logo);
echo "Full Path: " . $path . "\n";

if (file_exists($path)) {
    echo "File Exists: YES\n";
} else {
    echo "File Exists: NO\n";
}

$storagePath = Storage::disk('public')->path('logo/' . $empresa->logo);
echo "Storage Disk Path: " . $storagePath . "\n";
