<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        if (function_exists('tenant') && tenant()) {
            $this->call(SistemaCoreSeeder::class);
            $this->call(PerfilesSeeder::class);
            $this->call(AdminUserSeeder::class);
            return;
        }

        $this->call(PlanUnicoSeeder::class);

        $email = env('SEED_CENTRAL_ADMIN_EMAIL') ?: 'admin@adlatere.ec';
        $password = env('SEED_CENTRAL_ADMIN_PASSWORD') ?: 'AdlatereAdmin2026!';
        $name = env('SEED_CENTRAL_ADMIN_NAME') ?: 'Super Admin';

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'profile' => 'Admin',
                'password' => Hash::make($password),
            ]
        );

        // Mantiene operativo el acceso si el seeder se ejecuta de nuevo.
        $admin->forceFill([
            'name' => $name,
            'profile' => 'Admin',
            'password' => Hash::make($password),
        ])->save();

        $this->command?->info("Super admin central: {$email}");
        $this->command?->info('Contraseña: la configurada en SEED_CENTRAL_ADMIN_PASSWORD o el valor por defecto del seeder.');
    }
}
