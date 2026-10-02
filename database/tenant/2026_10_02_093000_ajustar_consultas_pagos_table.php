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
        // 1. Reubicar valores del enum viejo (la tabla puede tener datos de prueba)
        DB::table('consultas')->where('estado_pago', 'para_facturar')->update(['estado_pago' => 'pendiente']);
        DB::table('consultas')->where('estado_pago', 'facturado')->update(['estado_pago' => 'pendiente']);
        // 2. Nuevo enum: pendiente | pagada | facturada | no_factura
        DB::statement("ALTER TABLE consultas MODIFY estado_pago ENUM('pendiente','pagada','facturada','no_factura') DEFAULT 'pendiente'");
        // 3. Datos del cobro en recepción (como caja médica)
        Schema::table('consultas', function (Blueprint $table) {
            $table->date('fecha_pago')->nullable()->after('costo');
            $table->string('metodo_pago', 30)->nullable()->after('fecha_pago');
            $table->string('comprobante_ruta')->nullable()->after('metodo_pago');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropColumn(['fecha_pago', 'metodo_pago', 'comprobante_ruta']);
        });
        DB::statement("ALTER TABLE consultas MODIFY estado_pago ENUM('pendiente','facturado') DEFAULT 'pendiente'");
    }
};
