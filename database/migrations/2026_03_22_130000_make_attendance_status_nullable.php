<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attendance_items MODIFY status ENUM('H','I','S','A') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendance_items MODIFY status ENUM('H','I','S','A') NOT NULL DEFAULT 'H'");
    }
};
