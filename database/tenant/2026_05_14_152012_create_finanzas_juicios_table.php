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
        Schema::create('finanzas_juicios', function (Blueprint $table) {
             $table->id();
            $table->foreignId('juicio_id')->constrained('juicios')->onDelete('cascade');
            $table->decimal('honorarios_totales', 10, 2)->default(0);
            $table->decimal('gastos_extras', 10, 2)->default(0);
            $table->text('notas_acuerdo')->nullable();
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
        Schema::dropIfExists('finanzas_juicios');
    }
};
