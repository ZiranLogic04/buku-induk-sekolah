<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $query = Alumni::with('student');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $alumni = $query->orderByDesc('id')->get();

        return response()->json($alumni);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'tahun_lulus' => 'required|string|max:20',
            'status' => 'required|in:Kuliah,Bekerja,Wirausaha,Lainnya',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $alumni = Alumni::create($validated);

        return response()->json([
            'message' => 'Data alumni berhasil ditambahkan.',
            'data' => $alumni->load('student'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $alumni = Alumni::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'tahun_lulus' => 'required|string|max:20',
            'status' => 'required|in:Kuliah,Bekerja,Wirausaha,Lainnya',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $alumni->update($validated);

        return response()->json([
            'message' => 'Data alumni berhasil diperbarui.',
            'data' => $alumni->load('student'),
        ]);
    }

    public function destroy($id)
    {
        $alumni = Alumni::findOrFail($id);
        $alumni->delete();

        return response()->json(['message' => 'Data alumni berhasil dihapus.']);
    }
}
