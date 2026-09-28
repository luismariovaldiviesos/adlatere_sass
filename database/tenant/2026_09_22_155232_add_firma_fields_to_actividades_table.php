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
        Schema::table('actividades', function (Blueprint $table) {
            $table->enum('estado_firma', ['no_requerida','pendiente','firmada','rechazada'])
          ->default('no_requerida')->after('contenido');
            $table->foreignId('firmado_por_user_id')->nullable()->constrained('users')->after('estado_firma');
            $table->timestamp('firmado_en')->nullable()->after('firmado_por_user_id');
            $table->string('pdf_original_path')->nullable()->after('firmado_en');
            $table->string('pdf_firmado_path')->nullable()->after('pdf_original_path');
            $table->string('firma_hash')->nullable()->after('pdf_firmado_path');
            $table->json('firma_metadatos')->nullable()->after('firma_hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actividades', function (Blueprint $table) {
            //
        });
    }
};
