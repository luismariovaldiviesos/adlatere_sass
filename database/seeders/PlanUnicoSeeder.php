<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

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

        // Plan opcional para probar el registro público sin pasar por la pasarela.
        // Se crea solo si SAAS_SEED_TEST_PLAN=true.
        if (filter_var(env('SAAS_SEED_TEST_PLAN', false), FILTER_VALIDATE_BOOLEAN)) {
            Plan::updateOrCreate(['name' => 'Prueba Gratuita'], [
                'description' => 'Plan temporal para validar el registro y la activación inmediata.',
                'price' => 0.00,
                'invoice_limit' => 10,
            ]);
        }
    }
}
