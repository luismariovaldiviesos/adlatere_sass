<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $email = env('SEED_ADMIN_EMAIL') ?: 'admin@mail.com';
        $password = env('SEED_ADMIN_PASSWORD') ?: 'administrador';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => env('SEED_ADMIN_NAME') ?: 'Administrador',
                'ci' => env('SEED_ADMIN_CI') ?: '9999999999',
                'phone' => env('SEED_ADMIN_PHONE') ?: '0999999999',
                'profile' => 'Admin',
                'status' => 'ACTIVE',
                'password' => Hash::make($password),
            ]
        );

        $user->assignRole('Admin');
    }
}
