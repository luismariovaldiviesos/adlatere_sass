<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Stancl\Tenancy\Database\Models\Tenant;
use App\Models\Factura;
use App\Models\XmlFile;

foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);
    $f = Factura::where('secuencial', 'like', '%30')->first();
    if ($f) {
        echo "Tenant: " . $tenant->id . "\n";
        echo "Factura ID: " . $f->id . "\n";
        echo "Secuencial: " . $f->secuencial . "\n";
        echo "Clave Acceso: " . $f->claveAcceso . "\n";
        echo "Total: " . $f->total . "\n";
        
        $xml = XmlFile::where('factura_id', $f->id)->first();
        if ($xml) {
            echo "Directorio XML: " . $xml->directorio . "\n";
            echo "Estado XML: " . $xml->estado . "\n";
            echo "Error XML: " . $xml->error . "\n";
        }
        
        // Check for special characters in customer name
        if ($f->customer) {
            echo "Customer: " . $f->customer->businame . "\n";
        }
    }
}
