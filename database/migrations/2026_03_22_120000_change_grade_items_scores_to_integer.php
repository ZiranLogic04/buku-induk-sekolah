<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('nilai_angka')->nullable()->change();
            $table->unsignedSmallInteger('nilai_pengetahuan')->nullable()->change();
            $table->unsignedSmallInteger('nilai_keterampilan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('grade_items', function (Blueprint $table) {
            $table->decimal('nilai_angka', 5, 2)->nullable()->change();
            $table->decimal('nilai_pengetahuan', 5, 2)->nullable()->change();
            $table->decimal('nilai_keterampilan', 5, 2)->nullable()->change();
        });
    }
};
