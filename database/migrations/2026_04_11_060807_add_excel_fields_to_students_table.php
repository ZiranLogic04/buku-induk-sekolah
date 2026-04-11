<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Siswa Tambahan
            $table->string('agama')->nullable();
            $table->string('nkk')->nullable();
            $table->string('nik')->nullable(); // NIK Siswa
            $table->string('anak_ke')->nullable();
            $table->string('jml_saudara')->nullable();
            $table->string('penyakit')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('tinggal_bersama')->nullable();
            $table->string('no_akte')->nullable();

            // Ekstra Ayah
            $table->string('nik_ayah')->nullable();
            $table->string('tempat_lahir_ayah')->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('pendidikan_ayah')->nullable();
            $table->string('penghasilan_ayah')->nullable();
            $table->string('no_hp_ayah')->nullable();

            // Ekstra Ibu
            $table->string('nik_ibu')->nullable();
            $table->string('tempat_lahir_ibu')->nullable();
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('pendidikan_ibu')->nullable();

            // Wali
            $table->string('nama_wali')->nullable();
            $table->string('nik_wali')->nullable();
            $table->string('pekerjaan_wali')->nullable();
            $table->string('pendidikan_wali')->nullable();
            $table->string('hubungan_wali')->nullable();

            // Fisik & Minat
            $table->string('bb')->nullable();
            $table->string('tb')->nullable();
            $table->string('gol_darah')->nullable();
            $table->string('cita_cita')->nullable();
            $table->string('hobi')->nullable();

            // Kelengkapan Dokumen (Status ketersediaan dokumen)
            $table->string('dok_akte')->nullable();
            $table->string('dok_kk')->nullable();
            $table->string('dok_kip')->nullable();
            $table->string('dok_ktp_ortu')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'agama', 'nkk', 'nik', 'anak_ke', 'jml_saudara', 'penyakit', 'rt', 'rw', 'tinggal_bersama', 'no_akte',
                'nik_ayah', 'tempat_lahir_ayah', 'tanggal_lahir_ayah', 'pekerjaan_ayah', 'pendidikan_ayah', 'penghasilan_ayah', 'no_hp_ayah',
                'nik_ibu', 'tempat_lahir_ibu', 'tanggal_lahir_ibu', 'pekerjaan_ibu', 'pendidikan_ibu',
                'nama_wali', 'nik_wali', 'pekerjaan_wali', 'pendidikan_wali', 'hubungan_wali',
                'bb', 'tb', 'gol_darah', 'cita_cita', 'hobi',
                'dok_akte', 'dok_kk', 'dok_kip', 'dok_ktp_ortu'
            ]);
        });
    }
};
