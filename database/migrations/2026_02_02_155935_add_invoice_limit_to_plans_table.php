<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            // Check if column exists to avoid errors if migration is re-run or partial
            if (!Schema::hasColumn('plans', 'invoice_limit')) {
                $table->integer('invoice_limit')->nullable()->after('price')->comment('Limite de facturas por mes (null = ilimitado)');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
             if (Schema::hasColumn('plans', 'invoice_limit')) {
                $table->dropColumn('invoice_limit');
             }
        });
    }
};
