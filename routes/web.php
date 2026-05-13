<?php

use App\Http\Controllers\LembagaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\StudentHistoryController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\TeachingAssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\DashboardController;

Route::prefix('api')->group(function () {
    Route::get('/tahun-ajarans', [PublicController::class, 'tahunAjaran']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth');

    // Public template downloads so window.open() does not fail with 401 Unauthorized
    Route::get('/admin/students/template', [\App\Http\Controllers\StudentController::class, 'downloadTemplate']);
    Route::get('/admin/teachers/template', [App\Http\Controllers\GuruController::class, 'downloadTemplate']);
});

Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/admin/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/admin/lembaga', [LembagaController::class, 'index']);
    Route::post('/admin/lembaga', [LembagaController::class, 'update']);
    Route::post('/admin/lembaga/logo', [LembagaController::class, 'uploadLogo']);
    Route::post('/admin/lembaga/tahun-ajaran', [LembagaController::class, 'storeTahunAjaran']);
    Route::post('/admin/lembaga/jurusan', [LembagaController::class, 'storeJurusan']);
    Route::delete('/admin/lembaga/logo', [LembagaController::class, 'deleteLogo']);
    
    // Kelas Routes
    Route::get('/admin/classes', [KelasController::class, 'index']);
    Route::post('/admin/classes', [KelasController::class, 'store']);
    Route::put('/admin/classes/{id}', [KelasController::class, 'update']);
    Route::delete('/admin/classes/{id}', [KelasController::class, 'destroy']);
    Route::post('/admin/classes/copy', [KelasController::class, 'copyFromPreviousYear']);

    // Guru Routes
    Route::get('/admin/teachers', [GuruController::class, 'index']);
    Route::post('/admin/teachers', [GuruController::class, 'store']);
    Route::post('/admin/teachers/import', [GuruController::class, 'import']);
    Route::put('/admin/teachers/{id}', [GuruController::class, 'update']);
    Route::delete('/admin/teachers/{id}', [GuruController::class, 'destroy']);

    // Subject Routes
    Route::get('/admin/subjects', [SubjectController::class, 'index']);
    Route::post('/admin/subjects', [SubjectController::class, 'store']);
    Route::put('/admin/subjects/{id}', [SubjectController::class, 'update']);
    Route::delete('/admin/subjects/{id}', [SubjectController::class, 'destroy']);

    // Student Routes
    Route::post('/admin/students/bulk-move', [\App\Http\Controllers\StudentController::class, 'bulkMove']);
    Route::get('/admin/students', [\App\Http\Controllers\StudentController::class, 'index']);
    Route::post('/admin/students', [\App\Http\Controllers\StudentController::class, 'store']);
    Route::post('/admin/students/import', [\App\Http\Controllers\StudentController::class, 'import']);
    Route::put('/admin/students/{id}', [\App\Http\Controllers\StudentController::class, 'update']);
    Route::delete('/admin/students/{id}', [\App\Http\Controllers\StudentController::class, 'destroy']);

    // Student History Routes
    Route::get('/admin/student-histories', [StudentHistoryController::class, 'index']);
    Route::post('/admin/student-histories', [StudentHistoryController::class, 'store']);
    Route::post('/admin/student-histories/bulk', [StudentHistoryController::class, 'bulkUpsert']);

    // Grades Routes
    Route::get('/admin/grades', [GradeController::class, 'index']);
    Route::post('/admin/grades', [GradeController::class, 'store']);

    // Mutations Routes
    Route::get('/admin/mutations', [MutationController::class, 'index']);
    Route::post('/admin/mutations', [MutationController::class, 'store']);
    Route::put('/admin/mutations/{id}', [MutationController::class, 'update']);
    Route::delete('/admin/mutations/{id}', [MutationController::class, 'destroy']);

    // Alumni Routes
    Route::get('/admin/alumni', [AlumniController::class, 'index']);
    Route::post('/admin/alumni', [AlumniController::class, 'store']);
    Route::put('/admin/alumni/{id}', [AlumniController::class, 'update']);
    Route::delete('/admin/alumni/{id}', [AlumniController::class, 'destroy']);

    // Teaching Assignments Routes
    Route::get('/admin/assignments', [TeachingAssignmentController::class, 'index']);
    Route::post('/admin/assignments', [TeachingAssignmentController::class, 'store']);
    Route::post('/admin/assignments/bulk', [TeachingAssignmentController::class, 'bulkUpsert']);
    Route::delete('/admin/assignments/{id}', [TeachingAssignmentController::class, 'destroy']);

    // Attendance Routes
    Route::get('/admin/attendances', [AttendanceController::class, 'index']);
    Route::post('/admin/attendances', [AttendanceController::class, 'store']);
    Route::post('/admin/attendances/export-pdf', [AttendanceController::class, 'exportPdf']);
    Route::get('/admin/attendances/download/{filename}', [AttendanceController::class, 'download']);

    // User Management Routes (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::post('/admin/users', [UserController::class, 'store']);
        Route::put('/admin/users/{id}', [UserController::class, 'update']);
        Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
    });
});

Route::middleware('auth')->group(function () {
    // Generate PDF (save to storage) - returns JSON
    Route::post('/admin/print-books/generate/{studentId}', [App\Http\Controllers\PrintController::class, 'generate']);
    Route::post('/admin/print-books/generate-class/{classId}', [App\Http\Controllers\PrintController::class, 'generateClass']);

    // Preview template HTML langsung di browser
    Route::get('/admin/print-books/preview/{studentId}', [App\Http\Controllers\PrintController::class, 'previewTemplate']);
    Route::get('/admin/print-books/preview-class/{classId}', [App\Http\Controllers\PrintController::class, 'previewClassTemplate']);

    // Upload PDF dari frontend (html2pdf)
    Route::post('/admin/print-books/upload-generated/{studentId}', [App\Http\Controllers\PrintController::class, 'uploadGenerated']);
    Route::post('/admin/print-books/upload-generated-class/{classId}', [App\Http\Controllers\PrintController::class, 'uploadGeneratedClass']);

    // Download saved PDF
    Route::get('/admin/print-books/download/{filename}', [App\Http\Controllers\PrintController::class, 'download']);
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
