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
            $this->call(AdminUserSeeder::class);
        } else {
            $this->call(PlanUnicoSeeder::class);

            $email = env('SEED_CENTRAL_ADMIN_EMAIL') ?: 'admin@adlatere.ec';
            $password = env('SEED_CENTRAL_ADMIN_PASSWORD') ?: 'administardor';

            $admin = \App\Models\User::firstOrCreate(
                ['email' => $email],
                ['name' => 'Super Admin', 'profile' => 'Admin', 'password' => bcrypt($password)]
            );

            // Mantiene operativo el acceso si el seeder se ejecuta de nuevo.
            $admin->forceFill([
                'name' => 'Super Admin',
                'profile' => 'Admin',
                'password' => bcrypt($password),
            ])->save();
        }
    }
}
