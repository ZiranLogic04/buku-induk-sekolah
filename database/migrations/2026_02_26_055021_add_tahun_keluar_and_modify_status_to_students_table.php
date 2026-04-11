<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('tahun_keluar')->nullable()->after('status');
        });

        // Expand status enum
        DB::statement("ALTER TABLE students MODIFY COLUMN status ENUM('Aktif', 'Lulus', 'Pindah', 'Keluar', 'Cuti', 'Dikeluarkan', 'Mengundurkan Diri') DEFAULT 'Aktif'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('tahun_keluar');
        });

        // Revert status enum
        DB::statement("ALTER TABLE students MODIFY COLUMN status ENUM('Aktif', 'Lulus', 'Pindah', 'Keluar', 'Cuti') DEFAULT 'Aktif'");
    }
};
