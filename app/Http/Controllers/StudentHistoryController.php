<?php

namespace App\Http\Controllers;

use App\Models\StudentHistory;
use Illuminate\Http\Request;

class StudentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentHistory::with(['student', 'kelas', 'tahunAjaran']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        $histories = $query->orderByDesc('id')->get();

        return response()->json($histories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'semester' => 'required|in:Ganjil,Genap',
            'kelas_id' => 'nullable|exists:kelas,id',
            'status' => 'nullable|in:Aktif,Lulus,Pindah,Keluar,Cuti,Dikeluarkan,Mengundurkan Diri',
            'tahun_keluar' => 'nullable|string|max:20',
        ]);

        $history = StudentHistory::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
                'semester' => $validated['semester'],
            ],
            $validated
        );

        return response()->json([
            'message' => 'Riwayat siswa berhasil disimpan.',
            'data' => $history->load(['student', 'kelas', 'tahunAjaran']),
        ]);
    }

    public function bulkUpsert(Request $request)
    {
        $validated = $request->validate([
            'histories' => 'required|array|min:1',
            'histories.*.student_id' => 'required|exists:students,id',
            'histories.*.tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'histories.*.semester' => 'required|in:Ganjil,Genap',
            'histories.*.kelas_id' => 'nullable|exists:kelas,id',
            'histories.*.status' => 'nullable|in:Aktif,Lulus,Pindah,Keluar,Cuti,Dikeluarkan,Mengundurkan Diri',
            'histories.*.tahun_keluar' => 'nullable|string|max:20',
        ]);

        $saved = [];
        foreach ($validated['histories'] as $item) {
            $saved[] = StudentHistory::updateOrCreate(
                [
                    'student_id' => $item['student_id'],
                    'tahun_ajaran_id' => $item['tahun_ajaran_id'],
                    'semester' => $item['semester'],
                ],
                $item
            );
        }

        return response()->json([
            'message' => 'Riwayat siswa berhasil disimpan.',
            'count' => count($saved),
        ]);
    }
}
