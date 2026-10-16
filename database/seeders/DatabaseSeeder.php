<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// Arranque limpio. Tenant: SOLO sistema (resto por Excel con carga:inicial).
// Central: planes oficiales + super admin.
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        if (function_exists('tenant') && tenant()) {
            $this->call(SistemaCoreSeeder::class);
            $this->call(PerfilesSeeder::class);
        } else {
            $this->call(PlanUnicoSeeder::class);

            \App\Models\User::firstOrCreate(
                ['email' => 'admin@facta.com'],
                ['name' => 'Super Admin', 'password' => bcrypt('password')]
            );
        }
    }
}
