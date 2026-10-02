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
    Schema::create('consultas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
        $table->foreignId('asunto_id')->constrained('asuntos');
        $table->foreignId('abogado_id')->nullable()->constrained('users')->nullOnDelete();

        $table->decimal('costo', 10, 2)->default(0.00);
        // pendiente: recién creada | para_facturar: marcada por asistente | facturado: borrador creado | no_factura: cortesía / no se cobra
        $table->enum('estado_pago', ['pendiente', 'para_facturar', 'facturado', 'no_factura'])->default('pendiente');
        // pendiente: por atender | atendida: abogado ya la trabajó | convertida: generó un juicio
        $table->enum('estado_atencion', ['pendiente', 'atendida', 'convertida'])->default('pendiente');
        $table->longText('notas')->nullable(); // hoja de trabajo del abogado (editor tipo Word)

        $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();
        $table->foreignId('juicio_id')->nullable()->constrained('juicios')->nullOnDelete();
        $table->dateTime('fecha_atencion')->nullable();

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
        Schema::dropIfExists('consultas');
    }
};
