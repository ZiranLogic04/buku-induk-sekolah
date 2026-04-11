<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Grade;
use App\Models\TahunAjaran;
use App\Models\Lembaga;
use App\Models\AttendanceItem;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class PrintController extends Controller
{
    /**
     * Generate PDF for a single student and SAVE it to storage.
     * Returns JSON with filename so frontend can enable download.
     */
    public function generate($studentId)
    {
        $student = Student::with('kelas')->findOrFail($studentId);

        $data = $this->getStudentPrintData($student);

        $jenjangStr = $data['lembaga']->jenjang ?? 'SMP';
        $kurikulumStr = str_ireplace('kurikulum ', '', $data['lembaga']->kurikulum ?? 'Merdeka');
        if ($kurikulumStr === '2013') $kurikulumStr = 'K13';
        $kurikulumStr = \Illuminate\Support\Str::studly($kurikulumStr);

        $viewName = "prints.{$jenjangStr}.{$kurikulumStr}.cetak-buku-induk";

        // Render Blade to HTML based on Jenjang & Kurikulum
        if (view()->exists($viewName)) {
            $html = view($viewName, $data)->render();
        } else {
            $html = view('prints.cetak-buku-induk-empty', $data)->render();
        }

        // Generate PDF via headless Chrome
        $filename = 'Buku_Induk_' . Str::slug($student->nama) . '_' . $student->nis . '.pdf';
        $storagePath = 'print-books/' . $filename;

        $pdf = Browsershot::html($html)
            ->setNodeBinary(config('app.node_binary', 'node'))
            ->setNpmBinary(config('app.npm_binary', 'npm'))
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();

        Storage::disk('local')->put($storagePath, $pdf);

        // Mark as generated
        $student->update(['last_generated_at' => now(), 'last_generated_filename' => $filename]);

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'message' => 'PDF berhasil di-generate.',
        ]);
    }

    /**
     * Download a previously generated PDF.
     */
    public function download($filename)
    {
        $storagePath = 'print-books/' . $filename;

        if (!Storage::disk('local')->exists($storagePath)) {
            abort(404, 'PDF belum di-generate. Silakan generate terlebih dahulu.');
        }

        $fullPath = Storage::disk('local')->path($storagePath);

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Generate PDF for ALL students in a class and save to storage.
     */
    public function generateClass($classId)
    {
        $students = Student::where('kelas_id', $classId)->get();
        $kelas = \App\Models\Kelas::find($classId);

        if (!$kelas || $students->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Kelas tidak ditemukan atau tidak ada siswa.'], 404);
        }

        $academicYear = session('academic_year');
        $semester = session('semester', 'Ganjil');
        $tahunAjaran = TahunAjaran::where('nama', $academicYear)->first();
        $lembaga = Lembaga::first();

        $waliKelas = null;
        if ($kelas->wali_kelas_id) {
            $waliKelas = Guru::find($kelas->wali_kelas_id);
        }

        $studentsData = [];
        foreach ($students as $student) {
            $data = $this->getStudentPrintData($student);
            $studentsData[] = [
                'student' => $data['student'],
                'grade' => $data['grade'],
                'gradeMap' => $data['gradeMap'],
                'attendanceSummary' => $data['attendanceSummary'],
            ];
            $student->update(['last_generated_at' => now()]);
        }

        $subjects = \App\Models\Subject::orderBy('urutan')->get();

        $viewData = [
            'studentsData' => $studentsData,
            'kelas' => $kelas,
            'lembaga' => $lembaga,
            'tahunAjaran' => $tahunAjaran,
            'semester' => $semester,
            'waliKelas' => $waliKelas,
            'subjects' => $subjects,
        ];

        $jenjangStr = $lembaga->jenjang ?? 'SMP';
        $kurikulumStr = str_ireplace('kurikulum ', '', $lembaga->kurikulum ?? 'Merdeka');
        if ($kurikulumStr === '2013') $kurikulumStr = 'K13';
        $kurikulumStr = \Illuminate\Support\Str::studly($kurikulumStr);

        $viewName = "prints.{$jenjangStr}.{$kurikulumStr}.cetak-buku-induk-kelas";

        if (view()->exists($viewName)) {
            $html = view($viewName, $viewData)->render();
        } else {
            $html = view('prints.cetak-buku-induk-kelas-empty', $viewData)->render();
        }

        $filename = 'Buku_Induk_Kelas_' . Str::slug($kelas->nama) . '.pdf';
        $storagePath = 'print-books/' . $filename;

        $pdf = Browsershot::html($html)
            ->setNodeBinary(config('app.node_binary', 'node'))
            ->setNpmBinary(config('app.npm_binary', 'npm'))
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();

        Storage::disk('local')->put($storagePath, $pdf);

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'message' => 'PDF Kelas ' . $kelas->nama . ' berhasil di-generate.',
        ]);
    }

    /**
     * Helper: gather all print data for a single student.
     */
    private function getStudentPrintData(Student $student)
    {
        $academicYear = session('academic_year');
        $semester = session('semester', 'Ganjil');

        $tahunAjaran = TahunAjaran::where('nama', $academicYear)->first();
        $lembaga = Lembaga::first();

        $waliKelas = null;
        if ($student->kelas && $student->kelas->wali_kelas_id) {
            $waliKelas = Guru::find($student->kelas->wali_kelas_id);
        }

        $grade = null;
        $gradeMap = [];
        if ($tahunAjaran) {
            $grade = Grade::with('items.subject')
                ->where('student_id', $student->id)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester', $semester)
                ->first();

            if ($grade) {
                foreach ($grade->items as $item) {
                    if ($item->subject) {
                        $gradeMap[$item->subject->nama] = $item;
                    }
                }
            }
        }

        $attendanceSummary = (object) ['sakit' => 0, 'ijin' => 0, 'alpha' => 0];
        if ($tahunAjaran) {
            $summary = AttendanceItem::where('student_id', $student->id)
                ->whereHas('attendance', function ($q) use ($tahunAjaran, $semester) {
                    $q->where('tahun_ajaran_id', $tahunAjaran->id)
                      ->where('semester', $semester);
                })
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END), 0) as sakit,
                    COALESCE(SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END), 0) as ijin,
                    COALESCE(SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END), 0) as alpha
                ")
                ->first();

            if ($summary) {
                $attendanceSummary = $summary;
            }
        }

        $subjects = \App\Models\Subject::orderBy('urutan')->get();

        return [
            'student' => $student,
            'grade' => $grade,
            'gradeMap' => $gradeMap,
            'attendanceSummary' => $attendanceSummary,
            'lembaga' => $lembaga,
            'tahunAjaran' => $tahunAjaran,
            'semester' => $semester,
            'waliKelas' => $waliKelas,
            'subjects' => $subjects,
        ];
    }
}
