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
            $table->timestamp('last_generated_at_genap')->nullable()->after('last_generated_filename');
            $table->string('last_generated_filename_genap')->nullable()->after('last_generated_at_genap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['last_generated_at_genap', 'last_generated_filename_genap']);
        });
    }
};
