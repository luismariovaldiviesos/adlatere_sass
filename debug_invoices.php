<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Stancl\Tenancy\Database\Models\Tenant;
use App\Models\Factura;
use App\Models\XmlFile;

foreach (Tenant::all() as $t) {
    tenancy()->initialize($t->id);
    echo "Tenant: {$t->id}\n";
    
    $invoices = Factura::latest()->take(10)->get();
    foreach ($invoices as $f) {
        $xml = XmlFile::where('factura_id', $f->id)->first();
        echo "  Invoice: #{$f->id}, Sec: {$f->secuencial}, Doc: {$f->codDoc}, State: " . ($xml ? $xml->estado : 'No XML record') . "\n";
        if ($xml && $xml->estado == 'firmado') {
             // Try to find the file
             $p1 = Storage::disk($xml->directorio)->path($xml->secuencial . '.xml');
             $p2 = Storage::disk($xml->directorio)->path($f->created_at->format('Y/m/') . $xml->secuencial . '.xml');
             
             if (file_exists($p1)) echo "    [FOUND] $p1\n";
             if (file_exists($p2)) echo "    [FOUND] $p2\n";
             
             // Search for ANY xml in firmados
             if (!file_exists($p1) && !file_exists($p2)) {
                 $files = Storage::disk($xml->directorio)->allFiles();
                 foreach ($files as $file) {
                     if (str_contains($file, $f->secuencial)) {
                          echo "    [FOUND NEARBY] " . Storage::disk($xml->directorio)->path($file) . "\n";
                     }
                 }
             }
        }
    }
}
