<?php

use Illuminate\Database\Migrations\Migration;
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
        // Libera la restricción ENUM para que profile acepte cualquier rol
        // creado desde el módulo Roles (Abogado, Asistente, etc.)
        DB::statement("ALTER TABLE users MODIFY profile VARCHAR(50) NOT NULL DEFAULT 'Admin'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('users')->whereNotIn('profile', ['Admin', 'Employee'])->update(['profile' => 'Employee']);
        DB::statement("ALTER TABLE users MODIFY profile ENUM('Admin','Employee') NOT NULL DEFAULT 'Admin'");
    }
};
