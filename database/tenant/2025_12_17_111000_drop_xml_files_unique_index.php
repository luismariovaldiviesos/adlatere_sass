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
        Schema::table('xml_files', function (Blueprint $table) {
            // Drop the unique index
            $table->dropUnique('xml_files_secuencial_unique');
            // We could add a composite unique index if needed, but not strictly necessary as ID is unique
            // $table->unique(['secuencial', 'tipo_doc']); // If we had tipo_doc 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xml_files', function (Blueprint $table) {
            $table->unique('secuencial');
        });
    }
};
