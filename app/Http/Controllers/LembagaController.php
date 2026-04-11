<?php

namespace App\Http\Controllers;

use App\Models\Lembaga;
use App\Models\TahunAjaran;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LembagaController extends Controller
{
    public function index()
    {
        $lembaga = Lembaga::firstOrCreate([], [
            'nama' => 'Sekolah Indonesia',
            'jenis' => 'Sekolah',
            'status' => 'Negeri',
            'kurikulum' => 'Kurikulum Merdeka',
            'tahun_ajaran' => date('Y') . '/' . (date('Y') + 1),
            'semester' => 'Ganjil',
            'jurusan' => 'Umum',
        ]);

        if ($lembaga->tahun_ajaran && !TahunAjaran::where('nama', $lembaga->tahun_ajaran)->exists()) {
            TahunAjaran::create([
                'nama' => $lembaga->tahun_ajaran,
                'aktif' => true,
            ]);
        }

        return response()->json([
            'lembaga' => $lembaga,
            'tahun_ajarans' => TahunAjaran::where('aktif', true)->pluck('nama'),
            'jurusans' => Jurusan::pluck('nama')
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jenis' => 'required|in:Sekolah,Madrasah',
            'status' => 'required|in:Negeri,Swasta',
            'npsn' => 'nullable|string|max:20',
            'nss' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'jenjang' => 'required|in:SD / MI,SMP / MTs,SMA / MA,SMK,SD,SMP,SMA,MI,MTs,MA',
            'kurikulum' => 'required|in:Kurikulum Merdeka,Kurikulum 2013',
            'nama_kepala_sekolah' => 'nullable|string|max:255',
            'nip_kepala_sekolah' => 'nullable|string|max:50',
            'tahun_ajaran' => 'required|string|exists:tahun_ajarans,nama',
            'semester' => 'required|in:Ganjil,Genap',
            'jurusan' => 'nullable|string|max:255',
            'logo' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ], [
            'nama.required' => 'Nama lembaga wajib diisi.',
            'nama.string' => 'Nama lembaga harus berupa teks.',
            'nama.max' => 'Nama lembaga maksimal 255 karakter.',
            'jenis.required' => 'Jenis lembaga wajib dipilih.',
            'jenis.in' => 'Pilihan jenis lembaga tidak valid.',
            'status.required' => 'Status lembaga wajib dipilih.',
            'status.in' => 'Pilihan status lembaga tidak valid.',
            'npsn.max' => 'NPSN maksimal 20 karakter.',
            'nss.max' => 'Nomor statistik maksimal 30 karakter.',
            'jenjang.required' => 'Jenjang pendidikan wajib dipilih.',
            'kurikulum.required' => 'Kurikulum wajib dipilih.',
            'kurikulum.in' => 'Pilihan kurikulum tidak valid.',
            'jenjang.in' => 'Pilihan jenjang pendidikan tidak valid.',
            'nama_kepala_sekolah.max' => 'Nama kepala sekolah maksimal 255 karakter.',
            'nip_kepala_sekolah.max' => 'NIP kepala sekolah maksimal 50 karakter.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.exists' => 'Tahun ajaran belum terdaftar.',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Pilihan semester tidak valid.',
            'jurusan.max' => 'Nama jurusan maksimal 255 karakter.',
            'logo.mimes' => 'Format logo harus berupa jpeg, png, jpg, gif, atau svg.',
            'logo.max' => 'Ukuran logo maksimal 5MB.',
        ]);

        $lembaga = Lembaga::firstOrCreate([], [
            'nama' => 'Sekolah Indonesia',
            'jenis' => 'Sekolah',
            'status' => 'Negeri',
            'kurikulum' => 'Kurikulum Merdeka',
            'tahun_ajaran' => date('Y') . '/' . (date('Y') + 1),
            'semester' => 'Ganjil',
            'jurusan' => 'Umum',
        ]);

        try {
            if ($request->hasFile('logo')) {
                if ($lembaga->logo_path) {
                    Storage::disk('public')->delete($lembaga->logo_path);
                }
                $path = $request->file('logo')->store('logos', 'public');
                $validated['logo_path'] = $path;
            }

            $lembaga->update($validated);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data lembaga.'
            ], 500);
        }

        return response()->json([
            'message' => 'Data lembaga berhasil diperbarui.',
            'data' => $lembaga
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ], [
            'logo.required' => 'File logo wajib diunggah.',
            'logo.mimes' => 'Format logo harus berupa jpeg, png, jpg, gif, atau svg.',
            'logo.max' => 'Ukuran logo maksimal 5MB.',
        ]);

        $lembaga = Lembaga::firstOrCreate([], [
            'nama' => 'Sekolah Indonesia',
            'jenis' => 'Sekolah',
            'status' => 'Negeri',
            'tahun_ajaran' => date('Y') . '/' . (date('Y') + 1),
            'semester' => 'Ganjil',
            'jurusan' => 'Umum',
        ]);

        if ($request->hasFile('logo')) {
            if ($lembaga->logo_path) {
                Storage::disk('public')->delete($lembaga->logo_path);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $lembaga->update(['logo_path' => $path]);

            return response()->json([
                'message' => 'Logo berhasil diperbarui.',
                'logo_url' => Storage::url($path)
            ]);
        }

        return response()->json(['message' => 'Tidak ada file logo yang diunggah.'], 400);
    }

    public function deleteLogo()
    {
        $lembaga = Lembaga::firstOrCreate([], [
            'nama' => 'Sekolah Indonesia',
            'jenis' => 'Sekolah',
            'status' => 'Negeri',
            'tahun_ajaran' => date('Y') . '/' . (date('Y') + 1),
            'semester' => 'Ganjil',
            'jurusan' => 'Umum',
        ]);

        if ($lembaga->logo_path) {
            Storage::disk('public')->delete($lembaga->logo_path);
            $lembaga->update(['logo_path' => null]);
            return response()->json(['message' => 'Logo berhasil dihapus.']);
        }

        return response()->json(['message' => 'Tidak ada logo untuk dihapus.'], 400);
    }

    public function storeTahunAjaran(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required|string|unique:tahun_ajarans,nama'
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.unique' => 'Tahun ajaran tersebut sudah ada.'
        ]);

        TahunAjaran::create(['nama' => $request->tahun_ajaran]);

        return response()->json(['message' => 'Tahun ajaran berhasil ditambahkan.']);
    }

    public function storeJurusan(Request $request)
    {
        $request->validate([
            'jurusan' => 'required|string|unique:jurusans,nama'
        ], [
            'jurusan.required' => 'Nama jurusan wajib diisi.',
            'jurusan.unique' => 'Jurusan tersebut sudah ada.'
        ]);

        Jurusan::create(['nama' => $request->jurusan]);

        return response()->json(['message' => 'Jurusan berhasil ditambahkan.']);
    }
}
