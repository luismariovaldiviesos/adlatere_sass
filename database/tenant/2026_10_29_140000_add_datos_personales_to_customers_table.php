<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'estado_civil')) {
                $table->string('estado_civil', 50)->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'profesion_ocupacion')) {
                $table->string('profesion_ocupacion', 150)->nullable()->after('estado_civil');
            }
            if (!Schema::hasColumn('customers', 'canton_id')) {
                $table->foreignId('canton_id')->nullable()->after('profesion_ocupacion')
                    ->constrained('cantones')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'canton_id')) {
                $table->dropConstrainedForeignId('canton_id');
            }
            if (Schema::hasColumn('customers', 'profesion_ocupacion')) {
                $table->dropColumn('profesion_ocupacion');
            }
            if (Schema::hasColumn('customers', 'estado_civil')) {
                $table->dropColumn('estado_civil');
            }
        });
    }
};
