<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('puede_firmar_despacho')
                ->default(false)
                ->after('firma_password');
        });

        $permissionId = DB::table('permissions')
            ->where('name', 'firmar_actividad_empresa')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId) {
            $userIds = DB::table('model_has_permissions')
                ->where('permission_id', $permissionId)
                ->pluck('model_id')
                ->unique()
                ->all();

            if (!empty($userIds)) {
                DB::table('users')
                    ->whereIn('id', $userIds)
                    ->update(['puede_firmar_despacho' => true]);
            }

            DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('puede_firmar_despacho');
        });
    }
};
