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
        $semester = session('semester', 'Ganjil');
        $suffix = $semester === 'Genap' ? '_Genap' : '';
        $filename = 'Buku_Induk_' . Str::slug($student->nama) . '_' . $student->nis . $suffix . '.pdf';
        $storagePath = 'print-books/' . $filename;

        $browsershot = Browsershot::html($html)
            ->setNodeBinary(config('app.node_binary', 'node'))
            ->setNpmBinary(config('app.npm_binary', 'npm'));

        if (env('CHROME_PATH')) {
            $browsershot->setChromePath(env('CHROME_PATH'));
        } elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $browsershot->setChromePath('C:\Program Files\Google\Chrome\Application\chrome.exe');
        }

        $pdf = $browsershot
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();

        Storage::disk('local')->put($storagePath, $pdf);

        // Mark as generated
        if ($semester === 'Genap') {
            $student->update(['last_generated_at_genap' => now(), 'last_generated_filename_genap' => $filename]);
        } else {
            $student->update(['last_generated_at' => now(), 'last_generated_filename' => $filename]);
        }

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

    public function previewTemplate($studentId)
    {
        $student = \App\Models\Student::with('kelas')->findOrFail($studentId);
        $lembaga = \App\Models\Lembaga::first();
        $academicYear = session('academic_year');
        $semester = session('semester', 'Ganjil');
        $tahunAjaran = \App\Models\TahunAjaran::where('nama', $academicYear)->first();

        $waliKelas = null;
        if ($student->kelas && $student->kelas->wali_kelas_id) {
            $waliKelas = \App\Models\Guru::find($student->kelas->wali_kelas_id);
        }

        $grade = null;
        $gradeMap = [];
        if ($tahunAjaran) {
            $grade = \App\Models\Grade::with('items.subject')
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
            $summary = \App\Models\AttendanceItem::where('student_id', $student->id)
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

        $viewData = [
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

        $jenjangStr = $lembaga->jenjang ?? 'SMP';
        $kurikulumStr = str_ireplace('kurikulum ', '', $lembaga->kurikulum ?? 'Merdeka');
        if ($kurikulumStr === '2013') $kurikulumStr = 'K13';
        $kurikulumStr = \Illuminate\Support\Str::studly($kurikulumStr);

        $viewName = "prints.{$jenjangStr}.{$kurikulumStr}.cetak-buku-induk";

        return view($viewName, $viewData);
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
            if ($semester === 'Genap') {
                $student->update(['last_generated_at_genap' => now()]);
            } else {
                $student->update(['last_generated_at' => now()]);
            }
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

        $suffix = $semester === 'Genap' ? '_Genap' : '';
        $filename = 'Buku_Induk_Kelas_' . Str::slug($kelas->nama) . $suffix . '.pdf';
        $storagePath = 'print-books/' . $filename;

        $browsershot = Browsershot::html($html)
            ->setNodeBinary(config('app.node_binary', 'node'))
            ->setNpmBinary(config('app.npm_binary', 'npm'));

        if (env('CHROME_PATH')) {
            $browsershot->setChromePath(env('CHROME_PATH'));
        } elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $browsershot->setChromePath('C:\Program Files\Google\Chrome\Application\chrome.exe');
        }

        $pdf = $browsershot
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

    public function uploadGenerated(\Illuminate\Http\Request $request, $studentId)
    {
        $student = \App\Models\Student::findOrFail($studentId);
        $request->validate([
            'pdf' => 'required|file',
        ]);

        $semester = session('semester', 'Ganjil');
        $suffix = $semester === 'Genap' ? '_Genap' : '';

        $filename = 'Buku_Induk_' . Str::slug($student->nama) . '_' . $student->nis . $suffix . '.pdf';
        $storagePath = 'print-books/' . $filename;

        $pdfContent = file_get_contents($request->file('pdf')->getRealPath());
        Storage::disk('local')->put($storagePath, $pdfContent);

        if ($semester === 'Genap') {
            $student->update(['last_generated_at_genap' => now(), 'last_generated_filename_genap' => $filename]);
        } else {
            $student->update(['last_generated_at' => now(), 'last_generated_filename' => $filename]);
        }

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'message' => 'PDF berhasil disimpan.',
        ]);
    }

    public function uploadGeneratedClass(\Illuminate\Http\Request $request, $classId)
    {
        $kelas = \App\Models\Kelas::findOrFail($classId);
        $request->validate([
            'pdf' => 'required|file',
        ]);

        $semester = session('semester', 'Ganjil');
        $suffix = $semester === 'Genap' ? '_Genap' : '';

        $filename = 'Buku_Induk_Kelas_' . Str::slug($kelas->nama) . $suffix . '.pdf';
        $storagePath = 'print-books/' . $filename;

        $pdfContent = file_get_contents($request->file('pdf')->getRealPath());
        Storage::disk('local')->put($storagePath, $pdfContent);

        // Update all students in class
        if ($semester === 'Genap') {
            \App\Models\Student::where('kelas_id', $classId)->update(['last_generated_at_genap' => now()]);
        } else {
            \App\Models\Student::where('kelas_id', $classId)->update(['last_generated_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'message' => 'PDF Kelas ' . $kelas->nama . ' berhasil disimpan.',
        ]);
    }

    public function previewClassTemplate($classId)
    {
        $kelas = \App\Models\Kelas::findOrFail($classId);
        $students = \App\Models\Student::where('kelas_id', $classId)->get();

        $academicYear = session('academic_year');
        $semester = session('semester', 'Ganjil');
        $tahunAjaran = \App\Models\TahunAjaran::where('nama', $academicYear)->first();
        $lembaga = \App\Models\Lembaga::first();

        $waliKelas = null;
        if ($kelas->wali_kelas_id) {
            $waliKelas = \App\Models\Guru::find($kelas->wali_kelas_id);
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
            return view($viewName, $viewData);
        } else {
            return view('prints.cetak-buku-induk-kelas-empty', $viewData);
        }
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
