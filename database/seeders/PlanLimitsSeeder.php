<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanLimitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Plan::updateOrCreate(
            ['name' => 'Básico'],
            ['price' => 4.99, 'invoice_limit' => 30, 'description' => "Hasta 30 facturas por ciclo de pago\nFacturación Electrónica SRI\nSoporte Básico"]
        );

        \App\Models\Plan::updateOrCreate(
            ['name' => 'Emprendedor'],
            ['price' => 14.99, 'invoice_limit' => 300, 'description' => "Hasta 300 facturas por ciclo de pago\nFacturación Electrónica SRI\nSoporte Prioritario"]
        );

        \App\Models\Plan::updateOrCreate(
            ['name' => 'Negocio'],
            ['price' => 24.99, 'invoice_limit' => null, 'description' => "Facturación Ilimitada\nFacturación Electrónica SRI\nSoporte Dedicado\nPersonalización de Logo"]
        );
    }
}
