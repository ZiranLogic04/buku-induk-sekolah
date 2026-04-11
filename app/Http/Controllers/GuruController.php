<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GuruImport;
use App\Exports\GuruTemplateExport;

class GuruController extends Controller
{
    public function downloadTemplate()
    {
        return Excel::download(new GuruTemplateExport, 'template_import_guru.xlsx');
    }

    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new GuruImport, $request->file('file'));
            return response()->json(['message' => 'Data berhasil diimport', 'count' => 'semua'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal import data: ' . $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $gurus = Guru::latest()->get()->map(function ($guru) {
            try {
                $guru->plain_password = $guru->password_text ? Crypt::decryptString($guru->password_text) : '-';
            } catch (\Exception $e) {
                $guru->plain_password = '-';
            }
            unset($guru->password, $guru->password_text);
            return $guru;
        });
        return response()->json($gurus);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:255',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'status_kepegawaian' => 'nullable|string|max:50',
            'tanggal_masuk' => 'nullable|date',
            'password' => 'nullable|string|min:8',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tanggal_masuk.date' => 'Format tanggal masuk tidak valid.',
        ]);

        // Auto-generate password if empty
        $plainPassword = $validated['password'] ?? Str::random(8);
        
        $validated['password'] = Hash::make($plainPassword);
        $validated['password_text'] = Crypt::encryptString($plainPassword);

        Guru::create($validated);

        return response()->json(['message' => 'Data guru berhasil ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        $validated = $request->validate([
            'nip' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:255',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'status_kepegawaian' => 'nullable|string|max:50',
            'tanggal_masuk' => 'nullable|date',
            'password' => 'nullable|string|min:8',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tanggal_masuk.date' => 'Format tanggal masuk tidak valid.',
        ]);

        if (!empty($validated['password'])) {
            $plainPassword = $validated['password'];
            $validated['password'] = Hash::make($plainPassword);
            $validated['password_text'] = Crypt::encryptString($plainPassword);
        } else {
            unset($validated['password']);
        }

        $guru->update($validated);

        return response()->json(['message' => 'Data guru berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $guru = Guru::findOrFail($id);
        $guru->delete();

        return response()->json(['message' => 'Data guru berhasil dihapus']);
    }
}
