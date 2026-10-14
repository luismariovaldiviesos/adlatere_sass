<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

// Usuarios reales del despacho. Idempotente: ubica por email y si no
// por nombre (por si cambió el correo), actualiza datos y sincroniza rol.
// NOTA: cambiar estas contraseñas tras el primer ingreso.
class UsuariosSistemaSeeder extends Seeder
{
    public function run()
    {
        $usuarios = [
            [
                'name' => 'Piedad Orellana',
                'ci' => '0101866846',
                'phone' => '78787878',
                'email' => 'piedad@mail.com',
                'profile' => 'Abogado',
                'status' => 'ACTIVE',
                'password' => bcrypt('piedad'),
            ],
            [
                'name' => 'Ximena Chocho',
                'ci' => '0103844494',
                'phone' => '0874102355',
                'email' => 'xime1588@hotmail.com',
                'profile' => 'Abogado',
                'status' => 'ACTIVE',
                'password' => bcrypt('ximena'),
            ],
            [
                'name' => 'Patricia Cabrera',
                'ci' => '0102696184',
                'phone' => null, // conservar el existente si lo hay
                'email' => 'patricia@mail.com',
                'profile' => 'Asistente',
                'status' => 'ACTIVE',
                'password' => bcrypt('patricia'),
            ],
        ];

        foreach ($usuarios as $d) {
            $user = User::where('email', $d['email'])->first()
                ?? User::where('name', $d['name'])->first();
            if ($user) {
                $user->name = $d['name'];
                $user->ci = $d['ci'];
                if ($d['phone']) {
                    $user->phone = $d['phone'];
                }
                $user->email = $d['email'];
                $user->profile = $d['profile'];
                $user->status = $d['status'];
                $user->password = $d['password'];
                $user->save();
            } else {
                $user = User::create(array_merge($d, ['phone' => $d['phone'] ?? '0000000000']));
            }
            if (Role::where('name', $d['profile'])->exists()) {
                $user->syncRoles([$d['profile']]);
            }
        }

        // Clientes: completar la cédula de 9 dígitos a 10 (dígito verificador 8)
        \App\Models\Customer::where('valueidenti', '010452587')
            ->update(['valueidenti' => '0104525878']);
    }
}
