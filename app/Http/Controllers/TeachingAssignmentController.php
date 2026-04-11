<?php

namespace App\Http\Controllers;

use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = TeachingAssignment::with(['kelas', 'subject', 'guru', 'tahunAjaran']);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }
        $assignments = $query->orderBy('subject_id')->get();

        return response()->json($assignments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'subject_id' => 'required|exists:subjects,id',
            'guru_id' => 'nullable|exists:gurus,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        $assignment = TeachingAssignment::create([
            'kelas_id' => $validated['kelas_id'],
            'subject_id' => $validated['subject_id'],
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'] ?? null,
            'guru_id' => $validated['guru_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Penugasan berhasil disimpan.',
            'data' => $assignment->load(['kelas', 'subject', 'guru', 'tahunAjaran']),
        ]);
    }

    public function bulkUpsert(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
            'assignments' => 'required|array|min:1',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.guru_id' => 'nullable|exists:gurus,id',
        ]);

        foreach ($validated['assignments'] as $item) {
            TeachingAssignment::create([
                'kelas_id' => $validated['kelas_id'],
                'subject_id' => $item['subject_id'],
                'tahun_ajaran_id' => $validated['tahun_ajaran_id'] ?? null,
                'guru_id' => $item['guru_id'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Penugasan berhasil disimpan.',
            'count' => count($validated['assignments']),
        ]);
    }

    public function destroy($id)
    {
        $assignment = TeachingAssignment::findOrFail($id);
        $assignment->delete();

        return response()->json(['message' => 'Penugasan berhasil dihapus.']);
    }
}
