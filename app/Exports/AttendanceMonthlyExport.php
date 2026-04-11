<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Carbon;

class AttendanceMonthlyExport implements FromView, ShouldAutoSize
{
    protected $kelasId;
    protected $bulan;

    public function __construct($kelasId, $bulan)
    {
        $this->kelasId = $kelasId;
        $this->bulan = $bulan; // format Y-m
    }

    public function view(): View
    {
        $date = Carbon::createFromFormat('Y-m', $this->bulan);
        $daysInMonth = $date->daysInMonth;
        
        $students = Student::where('kelas_id', $this->kelasId)
            ->where('status', 'Aktif')
            ->orderBy('nama')
            ->get();

        $attendances = Attendance::with('items')
            ->where('kelas_id', $this->kelasId)
            ->whereYear('tanggal', $date->year)
            ->whereMonth('tanggal', $date->month)
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->format('j');
            });

        $matrix = [];
        foreach ($students as $student) {
            $row = [
                'nama' => $student->nama,
                'nis' => $student->nis,
                'days' => [],
                'rekap' => ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'L' => 0]
            ];

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $status = '';
                if (isset($attendances[$i])) {
                    $item = $attendances[$i]->items->where('student_id', $student->id)->first();
                    $status = $item ? $item->status : '';
                    if ($status && isset($row['rekap'][$status])) {
                        $row['rekap'][$status]++;
                    }
                }
                $row['days'][$i] = $status;
            }
            $matrix[] = $row;
        }

        return view('exports.attendance_monthly', [
            'matrix' => $matrix,
            'daysInMonth' => $daysInMonth,
            'bulanText' => $date->translatedFormat('F Y'),
            'kelas' => \App\Models\Kelas::find($this->kelasId)
        ]);
    }
}
