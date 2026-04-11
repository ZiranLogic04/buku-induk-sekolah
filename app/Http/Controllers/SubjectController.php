<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = \App\Models\Subject::orderBy('urutan')->get();
        $jurusans = \App\Models\Jurusan::pluck('nama');

        // Logic Tingkat dari Lembaga
        $lembaga = \App\Models\Lembaga::first();
        $jenjang = $lembaga ? $lembaga->jenjang : 'SMA';
        $tingkatLevels = ['10', '11', '12']; // Default

        if ($jenjang && (str_contains(strtoupper($jenjang), 'SMP') || str_contains(strtoupper($jenjang), 'MTS'))) {
            $tingkatLevels = ['7', '8', '9'];
        } elseif ($jenjang && (str_contains(strtoupper($jenjang), 'SD') || str_contains(strtoupper($jenjang), 'MI'))) {
            $tingkatLevels = ['1', '2', '3', '4', '5', '6'];
        }

        return response()->json([
            'subjects' => $subjects,
            'jurusans' => $jurusans,
            'tingkatLevels' => $tingkatLevels
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'singkatan' => 'nullable|string',
            'kelompok' => 'nullable|string',
            'tingkat' => 'nullable|array',
            'jurusan' => 'nullable|string',
            'urutan' => 'nullable|integer',
        ]);

        $subject = Subject::create($validated);
        return response()->json([
            'message' => 'Mata pelajaran berhasil ditambahkan.',
            'data' => $subject
        ]);
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'required|string',
            'singkatan' => 'nullable|string',
            'kelompok' => 'nullable|string',
            'tingkat' => 'nullable|array',
            'jurusan' => 'nullable|string',
            'urutan' => 'nullable|integer',
        ]);

        $subject->update($validated);
        return response()->json([
            'message' => 'Mata pelajaran berhasil diperbarui.',
            'data' => $subject
        ]);
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();
        return response()->json(['message' => 'Mata pelajaran berhasil dihapus.']);
    }
}
