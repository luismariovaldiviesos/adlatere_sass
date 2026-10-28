<?php

namespace App\Console\Commands;

use Database\Seeders\PerfilesSeeder;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SincronizarPermisosPerfiles extends Command
{
    protected $signature = 'permisos:perfiles {--force : Reemplaza la matriz exacta y elimina permisos fuera de ella}';

    protected $description = 'Aplica la matriz de permisos de los perfiles base';

    public function handle()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['Admin', 'Abogado', 'Asistente'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $force = (bool) $this->option('force');

        PerfilesSeeder::aplicarPerfil('Abogado', $force);
        PerfilesSeeder::aplicarPerfil('Asistente', $force);

        $admin = Role::findByName('Admin');
        if ($force) {
            $admin->syncPermissions(Permission::all());
        } else {
            $admin->givePermissionTo(Permission::all());
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('Matriz de permisos aplicada' . ($force ? ' (modo exacto)' : ' (modo aditivo)') . '.');

        return 0;
    }
}
