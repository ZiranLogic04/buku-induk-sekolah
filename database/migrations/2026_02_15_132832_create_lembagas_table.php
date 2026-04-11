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
        Schema::create('lembaga', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->default('Sekolah Indonesia');
            $table->enum('jenis', ['Sekolah', 'Madrasah'])->default('Sekolah');
            $table->enum('status', ['Negeri', 'Swasta'])->default('Negeri');
            $table->string('npsn')->nullable();
            $table->string('nss')->nullable();
            $table->text('alamat')->nullable();
            $table->enum('jenjang', ['SD / MI', 'SMP / MTs', 'SMA / MA', 'SMK'])->default('SMA / MA');
            $table->string('nama_kepala_sekolah')->nullable();
            $table->string('nip_kepala_sekolah')->nullable();
            
            $table->string('tahun_ajaran')->default(date('Y') . '/' . (date('Y') + 1));
            $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lembaga');
    }
};
