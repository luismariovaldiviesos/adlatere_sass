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
    
    foreach ([46, 47, 48] as $sec) {
        $secStr = str_pad($sec, 9, '0', STR_PAD_LEFT);
        $f = Factura::where('secuencial', $secStr)->first();
        if ($f) {
            $xml = XmlFile::where('factura_id', $f->id)->first();
            if ($xml) {
                $path = Storage::disk($xml->directorio)->path($xml->secuencial . '.xml');
                echo "Tenant: {$t->id}, Secuencial: $secStr, ID: {$f->id}, Path: $path\n";
                if (!file_exists($path)) {
                    // Try with partitioning
                    $datePath = $f->created_at->format('Y/m/') . $xml->secuencial . '.xml';
                    $path2 = Storage::disk($xml->directorio)->path($datePath);
                    echo "  Checking DatePath: $path2 " . (file_exists($path2) ? "[FOUND]" : "[NOT FOUND]") . "\n";
                }
            } else {
                echo "Tenant: {$t->id}, Secuencial: $secStr, ID: {$f->id}, No XmlFile record\n";
            }
        }
    }
}
