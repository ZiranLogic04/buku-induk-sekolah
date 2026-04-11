<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class StudentTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        return new Collection([
            [
                '12345',          // nis
                '0012345678',     // nisn
                'Budi Santoso',   // nama
                'L',              // jenis_kelamin (L/P)
                'Jakarta',        // tempat_lahir
                '2010-01-01',     // tanggal_lahir (YYYY-MM-DD atau format Excel)
                'Jl. Mawar No. 1',// alamat
                'Ayah Budi',      // nama_ayah
                'Ibu Budi',       // nama_ibu
                '1'               // kelas_id (ID Kelas yang valid)
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'nis',
            'nisn',
            'nama',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'nama_ayah',
            'nama_ibu',
            'kelas_id'
        ];
    }
}
