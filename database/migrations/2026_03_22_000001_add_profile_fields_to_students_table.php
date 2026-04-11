<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('nisn');
            $table->date('tanggal_masuk')->nullable()->after('status');
            $table->enum('status_masuk', ['Baru', 'Pindahan'])->default('Baru')->after('tanggal_masuk');
            $table->string('asal_pindahan')->nullable()->after('status_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['foto', 'tanggal_masuk', 'status_masuk', 'asal_pindahan']);
        });
    }
};
