<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
            $table->enum('semester', ['Ganjil', 'Genap'])->nullable();
            $table->unsignedTinyInteger('jp_per_minggu')->nullable();
            $table->timestamps();

            $table->unique(['kelas_id', 'subject_id', 'tahun_ajaran_id', 'semester'], 'uniq_assignment_class_subject_term');
            $table->index(['tahun_ajaran_id', 'semester'], 'idx_assignment_tahun_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
