<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clear test data to avoid duplicate conflicts when removing semester.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('teaching_assignments')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['guru_id']);
            $table->dropForeign(['tahun_ajaran_id']);

            $table->dropUnique('uniq_assignment_class_subject_term');
            $table->dropIndex('idx_assignment_tahun_semester');
            $table->dropColumn('semester');

            $table->unique(['kelas_id', 'subject_id', 'tahun_ajaran_id'], 'uniq_assignment_class_subject_year');
            $table->index(['tahun_ajaran_id'], 'idx_assignment_tahun');

            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->foreign('guru_id')->references('id')->on('gurus')->nullOnDelete();
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajarans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['guru_id']);
            $table->dropForeign(['tahun_ajaran_id']);

            $table->dropUnique('uniq_assignment_class_subject_year');
            $table->dropIndex('idx_assignment_tahun');
            $table->enum('semester', ['Ganjil', 'Genap'])->nullable();

            $table->unique(['kelas_id', 'subject_id', 'tahun_ajaran_id', 'semester'], 'uniq_assignment_class_subject_term');
            $table->index(['tahun_ajaran_id', 'semester'], 'idx_assignment_tahun_semester');

            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->foreign('guru_id')->references('id')->on('gurus')->nullOnDelete();
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajarans')->nullOnDelete();
        });
    }
};
