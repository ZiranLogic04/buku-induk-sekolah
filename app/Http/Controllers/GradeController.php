<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Grade::with(['student', 'kelas', 'tahunAjaran', 'items.subject']);

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

        $grades = $query->orderByDesc('id')->get();

        return response()->json($grades);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'semester' => 'required|in:Ganjil,Genap',
            'kelas_id' => 'nullable|exists:kelas,id',
            'catatan' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.subject_id' => 'required_with:items|exists:subjects,id',
            'items.*.nilai_angka' => 'nullable|numeric|min:0|max:100',
            'items.*.nilai_pengetahuan' => 'nullable|numeric|min:0|max:100',
            'items.*.nilai_keterampilan' => 'nullable|numeric|min:0|max:100',
            'items.*.nilai_huruf' => 'nullable|string|max:5',
            'items.*.keterangan' => 'nullable|string|max:255',
            'items.*.keterangan_pengetahuan' => 'nullable|string|max:255',
            'items.*.keterangan_keterampilan' => 'nullable|string|max:255',
            'replace_items' => 'nullable|boolean',
        ]);

        $replaceItems = array_key_exists('replace_items', $validated) ? (bool) $validated['replace_items'] : true;

        return DB::transaction(function () use ($validated, $replaceItems) {
            $grade = Grade::updateOrCreate(
                [
                    'student_id' => $validated['student_id'],
                    'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
                    'semester' => $validated['semester'],
                ],
                [
                    'kelas_id' => $validated['kelas_id'] ?? null,
                    'catatan' => $validated['catatan'] ?? null,
                ]
            );

            if (!empty($validated['items'])) {
                $subjectIds = [];
                foreach ($validated['items'] as $item) {
                    $subjectIds[] = $item['subject_id'];
                    GradeItem::updateOrCreate(
                        [
                            'grade_id' => $grade->id,
                            'subject_id' => $item['subject_id'],
                        ],
                        [
                            'nilai_angka' => $item['nilai_angka'] ?? null,
                            'nilai_pengetahuan' => $item['nilai_pengetahuan'] ?? null,
                            'nilai_keterampilan' => $item['nilai_keterampilan'] ?? null,
                            'nilai_huruf' => $item['nilai_huruf'] ?? null,
                            'keterangan' => $item['keterangan'] ?? null,
                            'keterangan_pengetahuan' => $item['keterangan_pengetahuan'] ?? null,
                            'keterangan_keterampilan' => $item['keterangan_keterampilan'] ?? null,
                        ]
                    );
                }

                if ($replaceItems) {
                    GradeItem::where('grade_id', $grade->id)
                        ->whereNotIn('subject_id', $subjectIds)
                        ->delete();
                }
            }

            return response()->json([
                'message' => 'Nilai berhasil disimpan.',
                'data' => $grade->load(['student', 'kelas', 'tahunAjaran', 'items.subject']),
            ]);
        });
    }
}
