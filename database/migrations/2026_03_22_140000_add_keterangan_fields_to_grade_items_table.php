<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_items', function (Blueprint $table) {
            $table->string('keterangan_pengetahuan')->nullable()->after('keterangan');
            $table->string('keterangan_keterampilan')->nullable()->after('keterangan_pengetahuan');
        });
    }

    public function down(): void
    {
        Schema::table('grade_items', function (Blueprint $table) {
            $table->dropColumn(['keterangan_pengetahuan', 'keterangan_keterampilan']);
        });
    }
};
