<?php

namespace App\Http\Controllers;

use App\Models\Mutation;
use Illuminate\Http\Request;

class MutationController extends Controller
{
    public function index(Request $request)
    {
        $query = Mutation::with(['student', 'kelas', 'tahunAjaran']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        $mutations = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        return response()->json($mutations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:Masuk,Keluar',
            'dari_ke' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'kelas_id' => 'nullable|exists:kelas,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
            'semester' => 'nullable|in:Ganjil,Genap',
        ]);

        $mutation = Mutation::create($validated);

        return response()->json([
            'message' => 'Mutasi berhasil ditambahkan.',
            'data' => $mutation->load(['student', 'kelas', 'tahunAjaran']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $mutation = Mutation::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:Masuk,Keluar',
            'dari_ke' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'kelas_id' => 'nullable|exists:kelas,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
            'semester' => 'nullable|in:Ganjil,Genap',
        ]);

        $mutation->update($validated);

        return response()->json([
            'message' => 'Mutasi berhasil diperbarui.',
            'data' => $mutation->load(['student', 'kelas', 'tahunAjaran']),
        ]);
    }

    public function destroy($id)
    {
        $mutation = Mutation::findOrFail($id);
        $mutation->delete();

        return response()->json(['message' => 'Mutasi berhasil dihapus.']);
    }
}
