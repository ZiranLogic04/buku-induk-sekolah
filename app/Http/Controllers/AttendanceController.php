<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\Guru;
use App\Models\TahunAjaran;
use App\Models\Student;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['kelas', 'tahunAjaran', 'items.student']);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }
        if ($request->filled('bulan')) {
            $month = Carbon::createFromFormat('Y-m', $request->bulan)->startOfMonth();
            $query->whereBetween('tanggal', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()]);
        }
        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('bulan')) {
            $attendances = $query->orderBy('tanggal')->get();
            return response()->json($attendances);
        }

        $attendance = $query->orderByDesc('tanggal')->first();

        return response()->json($attendance);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
            'semester' => 'nullable|in:Ganjil,Genap',
            'items' => 'required|array|min:1',
            'items.*.student_id' => 'required|exists:students,id',
            'items.*.status' => 'nullable|in:H,I,S,A,L',
            'items.*.keterangan' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated) {
            $attendance = Attendance::updateOrCreate(
                [
                    'tanggal' => $validated['tanggal'],
                    'kelas_id' => $validated['kelas_id'],
                    'tahun_ajaran_id' => $validated['tahun_ajaran_id'] ?? null,
                    'semester' => $validated['semester'] ?? null,
                ],
                []
            );

            foreach ($validated['items'] as $item) {
                AttendanceItem::updateOrCreate(
                    [
                        'attendance_id' => $attendance->id,
                        'student_id' => $item['student_id'],
                    ],
                    [
                        'status' => $item['status'] ?? null,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]
                );
            }

            return response()->json([
                'message' => 'Absensi berhasil disimpan.',
                'data' => $attendance->load(['kelas', 'tahunAjaran', 'items.student']),
            ]);
        });
    }



    public function exportPdf(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required',
            'bulan' => 'required', // Y-m
            'tahun_ajaran_id' => 'required',
            'semester' => 'required',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        $lembaga = Lembaga::first();
        $tahunAjaran = TahunAjaran::find($request->tahun_ajaran_id);
        $semester = $request->semester;
        $bulan = $request->bulan;

        $students = Student::where('kelas_id', $kelas->id)->orderBy('nama')->get();
        $waliKelas = $kelas->wali_kelas_id ? Guru::find($kelas->wali_kelas_id) : null;

        // Get all attendances for the month
        $month = Carbon::createFromFormat('Y-m', $bulan);
        $startDate = $month->copy()->startOfMonth()->toDateString();
        $endDate = $month->copy()->endOfMonth()->toDateString();

        $attendances = Attendance::with('items')
            ->where('kelas_id', $kelas->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $data = [];
        foreach ($attendances as $att) {
            foreach ($att->items as $item) {
                $data[$item->student_id][$att->tanggal] = $item->status;
            }
        }

        $daysInMonth = $month->daysInMonth;
        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        $viewData = [
            'kelas' => $kelas,
            'lembaga' => $lembaga,
            'tahunAjaran' => $tahunAjaran,
            'semester' => $semester,
            'students' => $students,
            'waliKelas' => $waliKelas,
            'bulan' => $bulan,
            'days' => $days,
            'data' => $data,
        ];

        $html = view('prints.cetak-absen', $viewData)->render();

        $filename = 'Absensi_' . Str::slug($kelas->nama) . '_' . $bulan . '.pdf';
        $storagePath = 'print-attendances/' . $filename;

        $pdf = Browsershot::html($html)
            ->setNodeBinary(config('app.node_binary', 'node'))
            ->setNpmBinary(config('app.npm_binary', 'npm'))
            ->noSandbox() // Required for some Windows environments
            ->format('A4')
            ->landscape()
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();

        Storage::disk('local')->put($storagePath, $pdf);

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'message' => 'Laporan PDF berhasil di-generate.'
        ]);
    }

    public function download($filename)
    {
        $storagePath = 'print-attendances/' . $filename;

        if (!Storage::disk('local')->exists($storagePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download(Storage::disk('local')->path($storagePath));
    }
}
