<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            [
                'name' => 'Emprendedor',
                'price' => 30.00,
                'description' => "Facturación Ilimitada\nInventario Básico\n1 Usuario\nSoporte Email",
                'remote_plan_id' => null
            ],
            [
                'name' => 'Negocio',
                'price' => 45.00,
                'description' => "Facturación Ilimitada\nInventario Avanzado\n3 Usuarios\nReportes Excel\nSoporte WhatsApp",
                'remote_plan_id' => null
            ],
            [
                'name' => 'Empresarial',
                'price' => 80.00,
                'description' => "Todo Ilimitado\nMultisucursal\nAPI Access\nSoporte Prioritario 24/7\nBackups Diarios",
                'remote_plan_id' => null
            ]
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['name' => $plan['name']], // Check by name to avoid duplicates
                $plan
            );
        }
    }
}
