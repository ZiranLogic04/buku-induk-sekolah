<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $query = Kelas::with(['jurusan', 'tahunAjaran'])
            ->withCount(['students as siswa_count' => function ($q) {
                $q->where('status', 'Aktif');
            }])
            ->latest();
        $sessionYear = request()->session()->get('academic_year');
        if ($sessionYear) {
            $ta = TahunAjaran::where('nama', $sessionYear)->first();
            if ($ta) {
                $query->where('tahun_ajaran_id', $ta->id);
            }
        }
        $kelas = $query->get();
        
        Jurusan::firstOrCreate(['nama' => 'Umum']);
        $jurusans = Jurusan::orderByRaw("nama = 'Umum' desc")->orderBy('nama')->get();
        $tahun_ajarans = TahunAjaran::orderByDesc('id')->get();
        $gurus = \App\Models\Guru::all();
        $lembaga = \App\Models\Lembaga::first();

        return response()->json([
            'kelas' => $kelas,
            'jurusans' => $jurusans,
            'tahun_ajarans' => $tahun_ajarans,
            'gurus' => $gurus,
            'lembaga' => $lembaga,
            'session_academic_year' => $sessionYear,
            'session_semester' => session('semester', 'Ganjil'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tingkat' => 'required|string',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
            'ruangan' => 'nullable|string|max:255',
        ]);

        Kelas::create($validated);

        return response()->json(['message' => 'Kelas berhasil ditambahkan.']);
    }

    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tingkat' => 'required|string',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
            'ruangan' => 'nullable|string|max:255',
        ]);

        $kelas->update($validated);

        return response()->json(['message' => 'Kelas berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        return response()->json(['message' => 'Kelas berhasil dihapus.']);
    }

    public function copyFromPreviousYear(Request $request)
    {
        $request->validate([
            'source_year_id' => 'required|exists:tahun_ajarans,id',
            'target_year_id' => 'required|exists:tahun_ajarans,id',
        ]);

        if ($request->source_year_id == $request->target_year_id) {
            return response()->json(['message' => 'Tahun sumber dan target tidak boleh sama.'], 422);
        }

        $sourceClasses = Kelas::where('tahun_ajaran_id', $request->source_year_id)->get();
        
        if ($sourceClasses->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data kelas di tahun ajaran sumber.'], 422);
        }

        $copiedCount = 0;
        foreach ($sourceClasses as $sourceClass) {
            // Check if exists in target year with same name
            $exists = Kelas::where('tahun_ajaran_id', $request->target_year_id)
                ->where('nama', $sourceClass->nama)
                ->exists();
                
            if (!$exists) {
                Kelas::create([
                    'nama' => $sourceClass->nama,
                    'tingkat' => $sourceClass->tingkat,
                    'jurusan_id' => $sourceClass->jurusan_id,
                    'tahun_ajaran_id' => $request->target_year_id,
                    'wali_kelas_id' => $sourceClass->wali_kelas_id,
                    'ruangan' => $sourceClass->ruangan,
                ]);
                $copiedCount++;
            }
        }

        return response()->json([
            'message' => "Berhasil menyalin $copiedCount kelas ke tahun ajaran target.",
            'copied_count' => $copiedCount
        ]);
    }
}
