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
            // Check and add columns individually to handle partial migrations
            if (!Schema::hasColumn('gurus', 'alamat')) {
                $table->text('alamat')->nullable()->after('nuptk');
            }
            if (!Schema::hasColumn('gurus', 'nik')) {
                $table->string('nik')->nullable()->after('nip');
            }
            if (!Schema::hasColumn('gurus', 'no_hp')) {
                $table->string('no_hp')->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('gurus', 'email')) {
                $table->string('email')->nullable()->after('no_hp');
            }
            if (!Schema::hasColumn('gurus', 'pendidikan_terakhir')) {
                $table->string('pendidikan_terakhir')->nullable()->after('email');
            }
            if (!Schema::hasColumn('gurus', 'status_kepegawaian')) {
                $table->string('status_kepegawaian')->default('Honorer');
            }
            if (!Schema::hasColumn('gurus', 'tanggal_masuk')) {
                $table->date('tanggal_masuk')->nullable()->after('status_kepegawaian');
            }
            if (!Schema::hasColumn('gurus', 'password')) {
                $table->string('password')->nullable()->after('tanggal_masuk');
            }
            if (!Schema::hasColumn('gurus', 'password_text')) {
                $table->text('password_text')->nullable()->after('password');
            }

            // Change jenis_kelamin type from enum to string if it exists
            if (Schema::hasColumn('gurus', 'jenis_kelamin')) {
                 $table->string('jenis_kelamin')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn(['nik', 'no_hp', 'email', 'pendidikan_terakhir', 'tanggal_masuk', 'password', 'password_text']);
            // Skipping detailed reverse for change() to keep it simple
        });
    }
};
