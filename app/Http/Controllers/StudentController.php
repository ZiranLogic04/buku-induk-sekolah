<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentHistory;
use App\Models\TahunAjaran;
use App\Models\Lembaga;
use App\Models\Mutation;
use App\Models\Alumni;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentImport;
use App\Exports\StudentTemplateExport;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        // Only show active students in Data Siswa
        $query = Student::with('kelas')->where('status', 'Aktif');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->has('tahun_masuk') && $request->tahun_masuk != '') {
             $query->where('tahun_masuk', $request->tahun_masuk);
        }

        if ($request->has('nopaginate') && $request->nopaginate == 'true') {
            $students = $query->get();
        } else {
            $students = $query->paginate(10);
        }
        
        $classesQuery = \App\Models\Kelas::query();
        $sessionYear = request()->session()->get('academic_year');
        if ($sessionYear) {
            $ta = TahunAjaran::where('nama', $sessionYear)->first();
            if ($ta) {
                $classesQuery->where('tahun_ajaran_id', $ta->id);
            }
        }
        $classes = $classesQuery->get();
        $tahunAjarans = TahunAjaran::where('aktif', true)->pluck('nama');

        return response()->json([
            'students' => $students,
            'classes' => $classes,
            'tahun_ajarans' => $tahunAjarans,
        ]);
    }

    public function bulkMove(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:kelas,lulus,tinggal',
            'actions' => 'required|array|min:1',
            'actions.*.student_id' => 'required|exists:students,id',
            'actions.*.action' => 'required|in:Naik,Lulus,Tinggal'
        ]);

        [$tahunAjaran, $semesterAktif] = $this->getActiveTerm();
        $targetClassesMap = $request->target_kelas_id ? \App\Models\Kelas::find($request->target_kelas_id) : null;

        foreach ($request->actions as $actionData) {
            $student = Student::find($actionData['student_id']);
            if (!$student) continue;

            $oldStatus = $student->status;
            $oldKelasId = $student->kelas_id;
            $oldTahunAjaranId = $tahunAjaran?->id;

            if ($actionData['action'] === 'Naik' && $targetClassesMap) {
                // Naik ke kelas baru yang dituju
                $student->kelas_id = $targetClassesMap->id;
                $student->status = 'Aktif';
            } elseif ($actionData['action'] === 'Lulus') {
                // Status kelulusan, lepas kelas atau biarkan, tapi update status 'Lulus'
                $student->status = 'Lulus';
                $student->kelas_id = null; // Bisa dikosongkan jika lulus
            } elseif ($actionData['action'] === 'Tinggal') {
                // Tinggal tetap di kelas yang sama, ubah status atau biarkan Aktif (tidak rubah target_kelas_id)
                $student->status = 'Aktif';
            }
            $student->save();

            if ($tahunAjaran && $semesterAktif) {
                // History tahun ajaran sekarang (kelas asal)
                StudentHistory::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'tahun_ajaran_id' => $oldTahunAjaranId,
                        'semester' => $semesterAktif,
                    ],
                    [
                        'kelas_id' => $oldKelasId,
                        'status' => $student->status,
                        'tahun_keluar' => $request->tahun_keluar ?? null,
                    ]
                );

                // Jika naik kelas, simpan history tahun ajaran tujuan (kelas baru)
                if ($actionData['action'] === 'Naik' && $targetClassesMap) {
                    StudentHistory::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'tahun_ajaran_id' => $targetClassesMap->tahun_ajaran_id,
                            'semester' => $semesterAktif,
                        ],
                        [
                            'kelas_id' => $targetClassesMap->id,
                            'status' => 'Aktif',
                            'tahun_keluar' => null,
                        ]
                    );
                }
            }

            if ($oldStatus !== $student->status) {
                $this->handleAutoMutationAndAlumni($student, $oldStatus, $oldKelasId, $request->tahun_keluar);
            }
        }

        return response()->json(['message' => 'Status Pindah Kelas/Kelulusan Berhasil Dieksekusi']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'nullable|string|unique:students',
            'nisn' => 'nullable|string|unique:students',
            'nama' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal_masuk' => 'nullable|date',
            'status_masuk' => 'nullable|in:Baru,Pindahan',
            'asal_pindahan' => 'nullable|string|max:255',
            'tahun_masuk' => 'nullable|string|max:20',
        ]);

        if (($validated['status_masuk'] ?? 'Baru') === 'Pindahan' && empty($validated['asal_pindahan'])) {
            return response()->json([
                'message' => 'Asal pindahan wajib diisi jika status masuk Pindahan.',
                'errors' => ['asal_pindahan' => ['Asal pindahan wajib diisi jika status masuk Pindahan.']]
            ], 422);
        }

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('students', 'public');
        }

        $student = Student::create($validated);

        // Optional: auto-create history for current active year/semester
        [$tahunAjaran, $semesterAktif] = $this->getActiveTerm();
        if ($tahunAjaran && $semesterAktif) {
            StudentHistory::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'semester' => $semesterAktif,
                ],
                [
                    'kelas_id' => $student->kelas_id,
                    'status' => $student->status ?? 'Aktif',
                    'tahun_keluar' => $student->tahun_keluar ?? null,
                ]
            );
        }

        if (($validated['status_masuk'] ?? 'Baru') === 'Pindahan') {
            Mutation::create([
                'student_id' => $student->id,
                'tanggal' => date('Y-m-d'),
                'jenis' => 'Masuk',
                'dari_ke' => $validated['asal_pindahan'] ?? null,
                'keterangan' => 'Siswa pindahan',
                'kelas_id' => $student->kelas_id,
                'tahun_ajaran_id' => $tahunAjaran?->id,
                'semester' => $semesterAktif,
            ]);
        }

        return response()->json(['message' => 'Siswa berhasil ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $oldStatus = $student->status;
        $oldKelasId = $student->kelas_id;

        $validated = $request->validate([
            'nis' => 'nullable|string|unique:students,nis,' . $student->id,
            'nisn' => 'nullable|string|unique:students,nisn,' . $student->id,
            'nama' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'status' => 'required|in:Aktif,Lulus,Pindah,Keluar,Cuti,Dikeluarkan,Mengundurkan Diri',
            'tahun_keluar' => 'nullable|string|max:20',
            'tanggal_keluar' => 'nullable|date',
            'dari_ke' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'tanggal_masuk' => 'nullable|date',
            'status_masuk' => 'nullable|in:Baru,Pindahan',
            'asal_pindahan' => 'nullable|string|max:255',
            'tahun_masuk' => 'nullable|string|max:20',
            // Data Tambahan Excel
            'agama' => 'nullable|string',
            'nkk' => 'nullable|string',
            'nik' => 'nullable|string',
            'anak_ke' => 'nullable|string',
            'jml_saudara' => 'nullable|string',
            'penyakit' => 'nullable|string',
            'rt' => 'nullable|string',
            'rw' => 'nullable|string',
            'tinggal_bersama' => 'nullable|string',
            'no_akte' => 'nullable|string',
            'nik_ayah' => 'nullable|string',
            'tempat_lahir_ayah' => 'nullable|string',
            'tanggal_lahir_ayah' => 'nullable|date',
            'pekerjaan_ayah' => 'nullable|string',
            'pendidikan_ayah' => 'nullable|string',
            'penghasilan_ayah' => 'nullable|string',
            'no_hp_ayah' => 'nullable|string',
            'nik_ibu' => 'nullable|string',
            'tempat_lahir_ibu' => 'nullable|string',
            'tanggal_lahir_ibu' => 'nullable|date',
            'pekerjaan_ibu' => 'nullable|string',
            'pendidikan_ibu' => 'nullable|string',
            'nama_wali' => 'nullable|string',
            'nik_wali' => 'nullable|string',
            'pekerjaan_wali' => 'nullable|string',
            'pendidikan_wali' => 'nullable|string',
            'hubungan_wali' => 'nullable|string',
            'bb' => 'nullable|string',
            'tb' => 'nullable|string',
            'gol_darah' => 'nullable|string',
            'cita_cita' => 'nullable|string',
            'hobi' => 'nullable|string',
            'dok_akte' => 'nullable|string',
            'dok_kk' => 'nullable|string',
            'dok_kip' => 'nullable|string',
            'dok_ktp_ortu' => 'nullable|string',
        ]);

        if ($request->has('status') && in_array($validated['status'], ['Pindah', 'Keluar', 'Dikeluarkan', 'Mengundurkan Diri'], true) && empty($validated['dari_ke'])) {
            return response()->json([
                'message' => 'Nama sekolah tujuan / alasan wajib diisi.',
                'errors' => ['dari_ke' => ['Nama sekolah tujuan / alasan wajib diisi.']]
            ], 422);
        }

        if ($request->has('status_masuk') && ($validated['status_masuk'] ?? 'Baru') === 'Pindahan' && empty($validated['asal_pindahan'])) {
            return response()->json([
                'message' => 'Asal pindahan wajib diisi jika status masuk Pindahan.',
                'errors' => ['asal_pindahan' => ['Asal pindahan wajib diisi jika status masuk Pindahan.']]
            ], 422);
        }

        if ($request->hasFile('foto')) {
            if ($student->foto) {
                Storage::disk('public')->delete($student->foto);
            }
            $validated['foto'] = $request->file('foto')->store('students', 'public');
        }


        $student->update($validated);

        if ($oldStatus !== $student->status) {
            [$tahunAjaran, $semesterAktif] = $this->getActiveTerm();
            if ($tahunAjaran && $semesterAktif) {
                StudentHistory::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'tahun_ajaran_id' => $tahunAjaran->id,
                        'semester' => $semesterAktif,
                    ],
                    [
                        'kelas_id' => $oldKelasId,
                        'status' => $student->status,
                        'tahun_keluar' => $request->tahun_keluar ?? null,
                    ]
                );
            }

            $this->handleAutoMutationAndAlumni(
                $student,
                $oldStatus,
                $oldKelasId,
                $request->tahun_keluar,
                $request->tanggal_keluar,
                $request->dari_ke,
                $request->keterangan
            );
        }

        return response()->json(['message' => 'Data Siswa berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json(['message' => 'Data Siswa berhasil dihapus']);
    }

    public function downloadTemplate()
    {
        $filePath = public_path('DAFTAR_SISWA_2025-2026 Ganjil.xlsm');
        if (file_exists($filePath)) {
            return response()->download($filePath, 'Template_Buku_Induk_2025.xlsm');
        } else if (file_exists(base_path('DAFTAR_SISWA_2025-2026 Ganjil.xlsm'))) {
            return response()->download(base_path('DAFTAR_SISWA_2025-2026 Ganjil.xlsm'), 'Template_Buku_Induk_2025.xlsm');
        }

        return response()->json(['message' => 'Template file tidak ditemukan di server.'], 404);
    }

    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new StudentImport, $request->file('file'));
            return response()->json(['message' => 'Data Siswa berhasil diimport'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal import data: ' . $e->getMessage()], 500);
        }
    }

    private function getActiveTerm(): array
    {
        $lembaga = Lembaga::first();
        $tahunAjaran = null;
        $sessionYear = request()->session()->get('academic_year');
        if ($sessionYear) {
            $tahunAjaran = TahunAjaran::where('nama', $sessionYear)->first();
        }
        if (!$tahunAjaran && $lembaga && $lembaga->tahun_ajaran) {
            $tahunAjaran = TahunAjaran::where('nama', $lembaga->tahun_ajaran)->first();
        }
        if (!$tahunAjaran) {
            $tahunAjaran = TahunAjaran::where('aktif', true)->first();
        }
        $semester = request()->session()->get('semester') ?? $lembaga?->semester;

        return [$tahunAjaran, $semester];
    }

    private function handleAutoMutationAndAlumni(
        Student $student,
        string $oldStatus,
        ?int $oldKelasId,
        ?string $tahunKeluar = null,
        ?string $tanggalKeluar = null,
        ?string $dariKe = null,
        ?string $keterangan = null
    ): void
    {
        $statusToMutationType = [
            'Pindah' => 'Keluar',
            'Keluar' => 'Keluar',
            'Mengundurkan Diri' => 'Keluar',
            'Dikeluarkan' => 'Keluar',
        ];

        if ($student->status === 'Lulus') {
            [$tahunAjaran, $semesterAktif] = $this->getActiveTerm();
            $tahunLulus = $tahunKeluar;
            if (!$tahunLulus && $tahunAjaran) {
                $tahunLulus = $tahunAjaran->nama;
            }
            if (!$tahunLulus) {
                $tahunLulus = date('Y');
            }

            Alumni::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'tahun_lulus' => $tahunLulus,
                ],
                [
                    'status' => 'Lainnya',
                    'keterangan' => null,
                ]
            );
            return;
        }

        if (array_key_exists($student->status, $statusToMutationType)) {
            [$tahunAjaran, $semesterAktif] = $this->getActiveTerm();

            Mutation::create([
                'student_id' => $student->id,
                'tanggal' => $tanggalKeluar ?: date('Y-m-d'),
                'jenis' => $statusToMutationType[$student->status],
                'dari_ke' => $dariKe,
                'keterangan' => $keterangan,
                'kelas_id' => $oldKelasId,
                'tahun_ajaran_id' => $tahunAjaran?->id,
                'semester' => $semesterAktif,
            ]);
        }
    }
}
