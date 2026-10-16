<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

// Planes oficiales (solo SaaS central). Idempotente.
class PlanUnicoSeeder extends Seeder
{
    public function run()
    {
        Plan::updateOrCreate(['name' => 'Instalación Local'], [
            'description' => 'Licencia de por vida. Instalación en servidor propio.',
            'price' => 300.00,
            'invoice_limit' => null,
        ]);
        Plan::updateOrCreate(['name' => 'Servicio en la nube'], [
            'description' => 'Pago mensual.',
            'price' => 24.99,
            'invoice_limit' => null,
        ]);
    }
}
