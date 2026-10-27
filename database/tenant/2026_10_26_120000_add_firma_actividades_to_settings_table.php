<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('firma_actividades_modo', 20)
                ->default('manual')
                ->after('cert_password');
            $table->boolean('firma_actividades_mismo_certificado')
                ->default(true)
                ->after('firma_actividades_modo');
            $table->string('firma_actividades_cert_file')
                ->nullable()
                ->after('firma_actividades_mismo_certificado');
            $table->text('firma_actividades_cert_password')
                ->nullable()
                ->after('firma_actividades_cert_file');
        });

        foreach (DB::table('settings')->whereNotNull('cert_password')->get(['id', 'cert_password']) as $row) {
            try {
                Crypt::decryptString($row->cert_password);
            } catch (\Throwable $e) {
                DB::table('settings')->where('id', $row->id)->update([
                    'cert_password' => Crypt::encryptString($row->cert_password),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'firma_actividades_modo',
                'firma_actividades_mismo_certificado',
                'firma_actividades_cert_file',
                'firma_actividades_cert_password',
            ]);
        });
    }
};
