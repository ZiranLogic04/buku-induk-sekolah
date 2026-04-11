<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `lembaga` MODIFY `jenjang` ENUM('SD / MI','SMP / MTs','SMA / MA','SMK','SD','SMP','SMA','MI','MTs','MA') DEFAULT 'SMA / MA'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `lembaga` MODIFY `jenjang` ENUM('SD / MI','SMP / MTs','SMA / MA','SMK') DEFAULT 'SMA / MA'");
    }
};
