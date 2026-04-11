<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
            $table->enum('semester', ['Ganjil', 'Genap'])->nullable();
            $table->timestamps();

            $table->unique(['tanggal', 'kelas_id', 'tahun_ajaran_id', 'semester'], 'uniq_attendance_session');
            $table->index(['tahun_ajaran_id', 'semester'], 'idx_attendance_tahun_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
