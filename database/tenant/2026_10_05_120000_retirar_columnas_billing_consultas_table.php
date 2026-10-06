<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Retira las columnas paralelas no funcionales (estado_pago_nuevo,
     * requiere_factura): el flujo de cobro vive en estado_pago/fecha_pago/
     * metodo_pago/comprobante_ruta + factura_id.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consultas', function (Blueprint $table) {
            if (Schema::hasColumn('consultas', 'estado_pago_nuevo')) {
                $table->dropColumn('estado_pago_nuevo');
            }
            if (Schema::hasColumn('consultas', 'requiere_factura')) {
                $table->dropColumn('requiere_factura');
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
        // Sin reversa: eran columnas no utilizadas por ningún flujo.
    }
};
