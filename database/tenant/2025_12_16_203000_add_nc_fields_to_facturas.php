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
        Schema::table('facturas', function (Blueprint $table) {
            $table->unsignedBigInteger('factura_modificada_id')->nullable()->after('formaPago');
            $table->string('motivo_nc', 255)->nullable()->after('factura_modificada_id');
            // Foreign key relation (optional, helps with integrity but self-referencing can be tricky without cascade rules)
            // $table->foreign('factura_modificada_id')->references('id')->on('facturas'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facturas', function (Blueprint $table) {
             $table->dropColumn(['factura_modificada_id', 'motivo_nc']);
        });
    }
};
