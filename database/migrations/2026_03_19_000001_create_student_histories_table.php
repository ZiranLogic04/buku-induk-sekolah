<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->enum('status', [
                'Aktif',
                'Lulus',
                'Pindah',
                'Keluar',
                'Cuti',
                'Dikeluarkan',
                'Mengundurkan Diri'
            ])->default('Aktif');
            $table->string('tahun_keluar')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'tahun_ajaran_id', 'semester'], 'uniq_student_history');
            $table->index(['tahun_ajaran_id', 'semester'], 'idx_history_tahun_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_histories');
    }
};
