<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('nilai_pengetahuan')->nullable()->after('nilai_angka');
            $table->unsignedSmallInteger('nilai_keterampilan')->nullable()->after('nilai_pengetahuan');
        });
    }

    public function down(): void
    {
        Schema::table('grade_items', function (Blueprint $table) {
            $table->dropColumn(['nilai_pengetahuan', 'nilai_keterampilan']);
        });
    }
};
