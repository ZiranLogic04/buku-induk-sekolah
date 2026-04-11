<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentImport implements ToModel, WithStartRow
{
    protected $kelasMap = [];

    public function __construct()
    {
        // Cache kelas untuk mempercepat pencarian ID berdasarkan nama kelas
        $this->kelasMap = Kelas::pluck('id', 'nama_kelas')->mapWithKeys(function ($id, $nama) {
            return [strtolower(trim($nama)) => $id];
        })->toArray();
    }

    public function startRow(): int
    {
        // Data di excel dimulai dari baris ke-3
        return 3;
    }

    public function model(array $row)
    {
        // Validasi: jika kolom "NAMA SISWA" (Index 3 / D) kosong, abaikan baris
        if (empty($row[3])) {
            return null;
        }

        $nis = $row[1] ?? null;
        $nisn = $row[2] ?? null;

        // Validasi Unique NIS/NISN
        if (!empty($nis) && Student::where('nis', $nis)->exists()) {
            return null;
        }
        if (!empty($nisn) && Student::where('nisn', $nisn)->exists()) {
            return null;
        }

        // Penentuan Kelas. Coba cari id berdasarkan nama kelas di kolom BE (56)
        $kelasId = null;
        $kelasName = !empty($row[56]) ? strtolower(trim($row[56])) : null;
        if ($kelasName && isset($this->kelasMap[$kelasName])) {
            $kelasId = $this->kelasMap[$kelasName];
        } else if (is_numeric($row[56])) {
             // Jika yang ditulis id-nya
             $kelasId = $row[56];
        }

        return new Student([
            // Identitas Utama
            'nis'           => $nis,
            'nisn'          => $nisn,
            'nama'          => $row[3],
            'jenis_kelamin' => $this->transformJK($row[4] ?? 'L'),
            'tempat_lahir'  => $row[5] ?? null,
            'tanggal_lahir' => $this->transformDate($row[6] ?? null),
            'agama'         => $row[9] ?? null,
            'nkk'           => $row[10] ?? null,
            'nik'           => $row[11] ?? null,
            'anak_ke'       => $row[12] ?? null,
            'jml_saudara'   => $row[13] ?? null,
            'penyakit'      => $row[14] ?? null,

            // Alamat
            'alamat'        => $row[15] ?? null,
            'rt'            => $row[16] ?? null,
            'rw'            => $row[17] ?? null,
            'tinggal_bersama' => $row[19] ?? null,
            'no_akte'       => $row[20] ?? null,

            // Data Ayah
            'nama_ayah'          => $row[21] ?? null,
            'nik_ayah'           => $row[22] ?? null,
            'tempat_lahir_ayah'  => $row[23] ?? null,
            'tanggal_lahir_ayah' => $this->transformDate($row[24] ?? null),
            'pekerjaan_ayah'     => $row[25] ?? null,
            'pendidikan_ayah'    => $row[26] ?? null,
            'penghasilan_ayah'   => $row[27] ?? null,
            'no_hp_ayah'         => $row[28] ?? null,

            // Data Ibu
            'nama_ibu'           => $row[29] ?? null,
            'nik_ibu'            => $row[30] ?? null,
            'tempat_lahir_ibu'   => $row[31] ?? null,
            'tanggal_lahir_ibu'  => $this->transformDate($row[32] ?? null),
            'pekerjaan_ibu'      => $row[33] ?? null,
            'pendidikan_ibu'     => $row[34] ?? null,

            // Data Wali
            'nama_wali'          => $row[35] ?? null,
            'nik_wali'           => $row[36] ?? null,
            'pekerjaan_wali'     => $row[38] ?? null,
            'pendidikan_wali'    => $row[39] ?? null,
            'hubungan_wali'      => $row[40] ?? null,

            // Status Pendaftaran
            'status_masuk'       => $row[41] ?? 'Baru',
            'asal_pindahan'      => $row[43] ?? null,
            'tanggal_masuk'      => $this->transformDate($row[54] ?? null),
            'kelas_id'           => $kelasId,
            'status'             => 'Aktif',

            // Dokumen
            'dok_akte'           => $row[45] ?? null,
            'dok_kk'             => $row[46] ?? null,
            'dok_kip'            => $row[47] ?? null,
            'dok_ktp_ortu'       => $row[48] ?? null,

            // Fisik & Minat
            'bb'                 => $row[50] ?? null,
            'tb'                 => $row[51] ?? null,
            'gol_darah'          => $row[52] ?? null,
            'cita_cita'          => $row[57] ?? null,
            'hobi'               => $row[58] ?? null,
        ]);
    }

    private function transformJK($value)
    {
        $val = strtoupper(trim($value));
        if (in_array($val, ['L', 'LAKI-LAKI', 'LAKI LAKI'])) return 'L';
        if (in_array($val, ['P', 'PEREMPUAN'])) return 'P';
        return 'L';
    }

    private function transformDate($value)
    {
        if (!$value) return null;

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        try {
            return date('Y-m-d', strtotime(str_replace('/', '-', $value)));
        } catch (\Exception $e) {
            return null;
        }
    }
}
