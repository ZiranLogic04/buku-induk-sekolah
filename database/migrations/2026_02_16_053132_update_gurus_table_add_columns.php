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
        Schema::table('gurus', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('nip');
            $table->string('no_hp')->nullable()->after('alamat');
            $table->string('email')->nullable()->after('no_hp');
            $table->string('pendidikan_terakhir')->nullable()->after('email');
            $table->date('tanggal_masuk')->nullable()->after('status_kepegawaian');
            
            // Drop enum and re-add as string to support "Laki-laki"
            $table->dropColumn('jenis_kelamin');
        });

        Schema::table('gurus', function (Blueprint $table) {
            $table->string('jenis_kelamin')->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn(['nik', 'no_hp', 'email', 'pendidikan_terakhir', 'tanggal_masuk']);
            $table->dropColumn('jenis_kelamin');
        });
        
        Schema::table('gurus', function (Blueprint $table) {
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('nama');
        });
    }
};
