<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Student;
use App\Models\Kelas;
use App\Models\Lembaga;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $academicYear = $request->session()->get('academic_year');
        
        $totalTeachers = Guru::count();
        $totalStudents = Student::where('status', 'Aktif')->count();
        
        $kelasQuery = Kelas::query();
        if ($academicYear) {
            $kelasQuery->whereHas('tahunAjaran', function($q) use ($academicYear) {
                $q->where('nama', $academicYear);
            });
        }
        $totalClasses = $kelasQuery->count();
        
        $lembaga = Lembaga::first();

        return response()->json([
            'teachers_count' => $totalTeachers,
            'students_count' => $totalStudents,
            'classes_count' => $totalClasses,
            'academic_year' => $academicYear,
            'institution' => $lembaga,
            // Sample activities (could be expanded)
            'recent_activities' => [
                ['id' => 1, 'type' => 'student', 'action' => 'Siswa Baru ditambahkan', 'time' => 'Baru saja'],
                ['id' => 2, 'type' => 'grade', 'action' => 'Input nilai kelas X-A selesai', 'time' => '2 jam yang lalu'],
                ['id' => 3, 'type' => 'class', 'action' => 'Pemindahan kelas masal berhasil', 'time' => '5 jam yang lalu'],
            ]
        ]);
    }
}
