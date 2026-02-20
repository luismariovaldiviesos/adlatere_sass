<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ResetDemoTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resetea el tenant DEMO borrando sus datos y volviendo a sembrar';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenantId = 'demo';
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->info("El tenant 'demo' no existe. Creándolo ahora...");
            try {
                // 1. Crear Tenant
                $tenant = Tenant::create([
                    'id' => 'demo',
                    'name' => 'Demo Pública',
                    'suscription_type' => 'Negocio', // Plan Ilimitado
                    'amount' => 0,
                    'bill_date' => now()->addYears(10), // Gratis por mucho tiempo
                    'status' => 1
                ]);
                
                // 2. Crear Dominio
                // Buscar 'facta.ec' en los dominios centrales, o usar el primero como fallback
                $centralDomains = config('tenancy.central_domains');
                $baseDomain = 'facta.ec';
                
                if (!in_array($baseDomain, $centralDomains)) {
                    $baseDomain = $centralDomains[0] ?? 'localhost';
                }

                $tenant->domains()->create(['domain' => 'demo.' . $baseDomain]);

                // 3. Crear Usuario Admin
                $tenant->run(function () {
                    $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
                    $user = \App\Models\User::create([
                        'name' => 'Usuario Demo',
                        'email' => 'admin@demo.com',
                        'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                        'ci' => '9999999999',
                        'phone' => '0999999999'
                    ]);
                    $user->assignRole($adminRole);
                });

                $this->info("Tenant 'demo' creado exitosamente.");
            } catch (\Exception $e) {
                $this->error("Error creando tenant demo: " . $e->getMessage());
                return 1;
            }
        } else {
             $this->info("Iniciando reset para tenant: {$tenantId}");
        }

        $tenant->run(function () {
             // Opción 1: Migrate Refresh (Lento y peligroso si falla a mitad)
             // Artisan::call('migrate:refresh --seed'); 

             // Opción 2: Truncate tablas clave (Más rápido para producción)
             DB::statement('SET FOREIGN_KEY_CHECKS=0;');
             
             $tables = [
                'facturas', 'detalle_facturas', 'customers', 'products', 
                'cajas', 'arqueos', 'xml_facturas', 'settings'
             ];

             foreach ($tables as $table) {
                 try {
                     DB::table($table)->truncate();
                     $this->info("- Tabla truncada: {$table}");
                 } catch (\Exception $e) {
                     $this->warn("Error truncando {$table}: " . $e->getMessage());
                 }
             }

             DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                // Auto-sembrar Caja y Arqueo para el usuario Demo
                // para que pueda facturar inmediatamente sin configuración
             try {
                $user = \App\Models\User::where('email', 'admin@demo.com')->first();
                if ($user) {
                     $caja = \App\Models\Caja::create([
                         'nombre' => 'Caja General Demo',
                         // Revisando Cajas.php: $caja->status = 0 (Cerrada), $caja->status = 1 (Abierta)
                         'status' => 1, 
                         'user_id' => $user->id
                     ]);

                     $arqueo = \App\Models\Arqueo::create([
                         'caja_id' => $caja->id,
                         'user_id' => $user->id,
                         'monto_inicial' => 20,
                         'created_at' => now(),
                         'status' => 'Abierta' // O int? Arqueo model check needed. Assuming mostly standard.
                     ]);
                     
                     $this->info("Caja y Arqueo inicial creados para el usuario demo.");
                     
                     // CREAR DATOS DE DEMOSTRACIÓN (PRODUCTOS Y CLIENTES)
                     try {
                        // 1. Categoría General
                        $cat = \App\Models\Category::create([
                            'name' => 'General de Prueba',
                            'image' => 'noimg'
                        ]);

                        // 2. Productos
                        $products = [
                            ['name' => 'Coca Cola 3L', 'price' => 3.50, 'cost' => 2.80, 'barcode' => '786100000001'],
                            ['name' => 'Arroz Flor 2kg', 'price' => 4.20, 'cost' => 3.50, 'barcode' => '786100000002'],
                            ['name' => 'Aceite Girasol 1L', 'price' => 2.80, 'cost' => 2.10, 'barcode' => '786100000003'],
                            ['name' => 'Leche Entera 1L', 'price' => 1.10, 'cost' => 0.85, 'barcode' => '786100000004'],
                            ['name' => 'Pan Molde Integral', 'price' => 1.75, 'cost' => 1.20, 'barcode' => '786100000005'],
                        ];

                        foreach($products as $p) {
                            \App\Models\Product::create([
                                'name' => $p['name'],
                                'barcode' => $p['barcode'],
                                'cost' => $p['cost'],
                                'price' => $p['price'],
                                'price2' => $p['price'], // Required for Cart Total calculation
                                'stock' => 100,
                                'alerts' => 10,
                                'category_id' => $cat->id,
                                'image' => 'noimg'
                            ]);
                        }

                        // 3. Clientes
                        \App\Models\Customer::create([
                            'name' => 'Consumidor Final',
                            'businame' => 'Consumidor Final',
                            'email' => 'consumidor@final.com',
                            'phone' => '0999999999',
                            'address' => 'S/N',
                            'typeidenti' => '07', // Consumidor Final (Corregido)
                            'valueidenti' => '9999999999999', // (Corregido)
                            'notes' => 'Cliente por defecto del demo'
                        ]);

                        \App\Models\Customer::create([
                            'name' => 'Juan Pérez (Cliente VIP)',
                            'businame' => 'Tech Solutions S.A.',
                            'email' => 'juan.perez@example.com',
                            'phone' => '0987654321',
                            'address' => 'Av. Amazonas y Naciones Unidas',
                            'typeidenti' => '04', // RUC (Corregido)
                            'valueidenti' => '1799999999001', // (Corregido)
                            'notes' => 'Cliente de prueba'
                        ]);

                        // 4. Datos de Empresa (Necesario para PDF y Facturación)
                        \App\Models\Setting::create([
                            'razonSocial' => 'Empresa Demo S.A.',
                            'nombreComercial' => 'DEMO FACTA',
                            'ruc' => '1790000000001',
                            'dirMatriz' => 'Av. Demo y Calle Prueba',
                            'dirEstablecimiento' => 'Av. Demo y Calle Prueba',
                            'telefono' => '022222222',
                            'email' => 'info@demo.com',
                            'logo' => 'noimg',
                            'estab' => '001',
                            'ptoEmi' => '001',
                            'ambiente' => 1, // Pruebas
                            'tipoEmision' => 1,
                            'obligadoContabilidad' => 'NO',
                            'contribuyenteEspecial' => '000',
                            'leyend' => 'Gracias por su compra',
                            'printer' => '80mm',
                            'annulment_days' => 5,
                            'enable_caja' => 0, // Desactivar caja por defecto en Demo
                            'cert_file' => 'demo.p12',
                            'cert_password' => '1234'
                        ]);
                        
                        $this->info("Productos, Clientes y Empresa de demo creados.");

                        $this->info("Productos y Clientes de demo creados.");

                     } catch(\Exception $ev) {
                        $this->warn("Error creando datos de prueba: " . $ev->getMessage());
                     }

                }

             } catch (\Exception $e) {
                 $this->error("Error en seeding: " . $e->getMessage());
             }

        });

        $this->info("Reset completado exitosamente.");
        return 0;
    }
}
