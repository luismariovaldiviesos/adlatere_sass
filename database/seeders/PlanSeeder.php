<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // NO usar truncate en producción si hay planes personalizados
        // DB::table('plans')->truncate();

        $plans = [
            [
                'name' => 'Básico',
                'description' => 'Ideal para profesionales independientes. Límite: 30 Documentos/mes.',
                'price' => 1.00,
                'invoice_limit' => 30,
            ],
            [
                'name' => 'Emprendedor',
                'description' => 'Para negocios en crecimiento. Límite: 300 Documentos/mes.',
                'price' => 14.99,
                'invoice_limit' => 300,
            ],
            [
                'name' => 'Negocio',
                'description' => 'Facturación Ilimitada para Pymes de alto volumen.',
                'price' => 24.99,
                'invoice_limit' => null,
            ],
            [
                'name' => 'Full Local',
                'description' => 'Licencia de por vida. Instalación en servidor propio (On-Premise).',
                'price' => 300.00,
                'invoice_limit' => null,
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\Plan::updateOrCreate(
                ['name' => $plan['name']], // Buscar por nombre
                $plan                      // Actualizar/Crear datos
            );
        }
    }
}
