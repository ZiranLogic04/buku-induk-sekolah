<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('status', ['H', 'I', 'S', 'A'])->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['attendance_id', 'student_id'], 'uniq_attendance_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_items');
    }
};
