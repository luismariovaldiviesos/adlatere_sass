<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 'en_facturacion' = borrador creado, aún sin autorización SRI.
     * 'facturada' queda reservado para factura AUTORIZADA.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE consultas MODIFY estado_pago ENUM('pendiente','pagada','en_facturacion','facturada','no_factura') DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('consultas')->where('estado_pago', 'en_facturacion')->update(['estado_pago' => 'pendiente']);
        DB::statement("ALTER TABLE consultas MODIFY estado_pago ENUM('pendiente','pagada','facturada','no_factura') DEFAULT 'pendiente'");
    }
};
