<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unsignedSmallInteger('nilai_angka')->nullable();
            $table->string('nilai_huruf', 5)->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['grade_id', 'subject_id'], 'uniq_grade_subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_items');
    }
};
