<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class GuruTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Return a sample row
        return new Collection([
            [
                'Budi Santoso, S.Pd', // nama
                '198001012000031001', // nip
                '3201010101800001',   // nik
                'Laki-laki',          // jenis_kelamin
                'Bandung',            // tempat_lahir
                '1980-01-01',         // tanggal_lahir (YYYY-MM-DD or Excel Date)
                '081234567890',       // no_hp
                'budi@sekolah.sch.id',// email
                'Jl. Merdeka No. 1',  // alamat
                'S1',                 // pendidikan_terakhir
                'PNS',                // status_kepegawaian
                '2000-03-01',         // tanggal_masuk
                'rahasia123'          // password (opsional)
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'nama',
            'nip',
            'nik',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'no_hp',
            'email',
            'alamat',
            'pendidikan_terakhir',
            'status_kepegawaian',
            'tanggal_masuk',
            'password',
        ];
    }
}
