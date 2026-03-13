<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // \App\Models\Category::factory(5)->create();
        // \App\Models\Product::factory(20)->create();
         //\App\Models\User::factory(20)->create();
        // \App\Models\Customer::factory(20)->create();

        // \App\Models\Order::factory(20)->create()->each(function($order){
        //     $order->details()->create([
        //         'order_id' => $order->id,
        //         'product_id' => Product::all()->random()->id,
        //         'quantity' => $order->items,
        //         'price' => $order->total / $order->items
        //     ]);
        // });
        if (function_exists('tenant') && tenant()) {
            // Seeders para el INQUILINO (Tenant)
            $this->call(PermisosSeeder::class);
            $this->call(UserSeeder::class);
            $this->call(InicialSeeder::class);
            $this->call(PermisosSistemaSeeder::class);
            $this->call(AdlatereSeeder::class);
        } else {
            // Seeders para la CENTRAL (Landlord)
            $this->call(PlansTableSeeder::class);
            
            // Create Super Admin
            \App\Models\User::firstOrCreate(
                ['email' => 'admin@facta.com'],
                [
                    'name' => 'Super Admin',
                    'password' => bcrypt('password'),
                    // 'ci' => '9999999999', // Not present in central users table
                    // 'phone' => '0999999999',
                ]
            );
        }

    }
}
