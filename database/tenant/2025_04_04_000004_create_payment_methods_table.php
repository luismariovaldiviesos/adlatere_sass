<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2);
            $table->string('description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed data
        DB::table('payment_methods')->insert([
            ['code' => '01', 'description' => 'SIN UTILIZACION DEL SISTEMA FINANCIERO', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '15', 'description' => 'COMPENSACIÓN DE DEUDAS', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '16', 'description' => 'TARJETA DE DÉBITO', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '17', 'description' => 'DINERO ELECTRÓNICO', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '18', 'description' => 'TARJETA PREPAGO', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '19', 'description' => 'TARJETA DE CRÉDITO', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '20', 'description' => 'OTROS CON UTILIZACION DEL SISTEMA FINANCIERO', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '21', 'description' => 'ENDOSO DE TÍTULOS', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
};
