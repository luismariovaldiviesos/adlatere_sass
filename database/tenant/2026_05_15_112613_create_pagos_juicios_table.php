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
        Schema::create('pagos_juicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finanzas_juicios_id')->constrained('finanzas_juicios')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null'); // ¿Quién pagó físicamente?
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ¿Qué abogado registró el abono?
            $table->decimal('monto', 10, 2);
            $table->date('fecha_pago');
            $table->string('metodo_pago'); // Efectivo, Transferencia, Cheque
            $table->string('referencia_transaccion')->nullable(); // Número de comprobante o cheque
            $table->string('comprobante_ruta')->nullable(); // Archivo físico subido
            $table->string('estado')->default('Aprobado'); // Aprobado, Pendiente
            $table->text('notas')->nullable(); // Detalle opcional del abono
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pagos_juicios');
    }
};
