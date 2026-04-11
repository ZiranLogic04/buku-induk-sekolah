<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('jenis', [
                'Masuk',
                'Pindah',
                'Keluar',
                'Lulus',
                'Mengundurkan Diri',
                'Dikeluarkan'
            ]);
            $table->string('dari_ke')->nullable();
            $table->string('keterangan')->nullable();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
            $table->enum('semester', ['Ganjil', 'Genap'])->nullable();
            $table->timestamps();

            $table->index(['tahun_ajaran_id', 'semester'], 'idx_mutation_tahun_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutations');
    }
};
