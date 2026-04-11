<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('tahun_lulus');
            $table->enum('status', ['Kuliah', 'Bekerja', 'Wirausaha', 'Lainnya'])->default('Lainnya');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'tahun_lulus'], 'uniq_alumni_student_year');
            $table->index('tahun_lulus', 'idx_alumni_tahun_lulus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
