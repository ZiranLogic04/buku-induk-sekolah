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
        $this->kelasMap = Kelas::pluck('id', 'nama')->mapWithKeys(function ($id, $nama) {
            return [strtolower(trim($nama)) => $id];
        })->toArray();
    }

    public function startRow(): int
    {
        // Data di excel dimulai dari baris ke-2 (row 1 = header)
        return 2;
    }

    public function model(array $row)
    {
        // Kolom index (0-based) mapping ke template baru:
        // 0=NO, 1=NIS, 2=NISN, 3=NAMA SISWA, 4=L/P, 5=TEMPAT LAHIR, 6=TGL LAHIR,
        // 7=UMUR, 8=AGAMA, 9=NKK, 10=NIK, 11=ANAK KE, 12=JML SAUDARA,
        // 13=PENYAKIT, 14=ALAMAT, 15=RT, 16=RW, 17=TINGGAL BERSAMA, 18=NO AKTE,
        // 19=NAMA AYAH, 20=NIK AYAH, 21=TEMPAT LAHIR AYAH, 22=TGL LAHIR AYAH,
        // 23=PEKERJAAN AYAH, 24=PENDIDIKAN AYAH, 25=PENGHASILAN, 26=NO HP,
        // 27=NAMA IBU, 28=NIK IBU, 29=TEMPAT LAHIR IBU, 30=TGL LAHIR IBU,
        // 31=PEKERJAAN IBU, 32=PENDIDIKAN IBU,
        // 33=NAMA WALI, 34=NIK WALI, 35=PEKERJAAN WALI, 36=PENDIDIKAN WALI,
        // 37=HUBUNGAN, 38=STATUS MASUK, 39=DI TERIMA DI KELAS, 40=SEKOLAH ASAL,
        // 41=BB, 42=TB, 43=GOL DARAH, 44=TGL DAFTAR, 45=TGL MASUK,
        // 46=KELAS, 47=CITA-CITA, 48=HOBI

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

        // Penentuan Kelas. Coba cari id berdasarkan nama kelas di kolom KELAS (46) atau DI TERIMA DI KELAS (39)
        $kelasId = null;
        $kelasName = !empty($row[46]) ? strtolower(trim($row[46])) : (!empty($row[39]) ? strtolower(trim($row[39])) : null);
        if ($kelasName && isset($this->kelasMap[$kelasName])) {
            $kelasId = $this->kelasMap[$kelasName];
        } else if (!empty($row[46]) && is_numeric($row[46])) {
             // Jika yang ditulis id-nya
             $kelasId = $row[46];
        }

        return new Student([
            // Identitas Utama
            'nis'           => $nis,
            'nisn'          => $nisn,
            'nama'          => $row[3],
            'jenis_kelamin' => $this->transformJK($row[4] ?? 'L'),
            'tempat_lahir'  => $row[5] ?? null,
            'tanggal_lahir' => $this->transformDate($row[6] ?? null),
            'agama'         => $row[8] ?? null,
            'nkk'           => $row[9] ?? null,
            'nik'           => $row[10] ?? null,
            'anak_ke'       => $row[11] ?? null,
            'jml_saudara'   => $row[12] ?? null,
            'penyakit'      => $row[13] ?? null,

            // Alamat
            'alamat'        => $row[14] ?? null,
            'rt'            => $row[15] ?? null,
            'rw'            => $row[16] ?? null,
            'tinggal_bersama' => $row[17] ?? null,
            'no_akte'       => $row[18] ?? null,

            // Data Ayah
            'nama_ayah'          => $row[19] ?? null,
            'nik_ayah'           => $row[20] ?? null,
            'tempat_lahir_ayah'  => $row[21] ?? null,
            'tanggal_lahir_ayah' => $this->transformDate($row[22] ?? null),
            'pekerjaan_ayah'     => $row[23] ?? null,
            'pendidikan_ayah'    => $row[24] ?? null,
            'penghasilan_ayah'   => $row[25] ?? null,
            'no_hp_ayah'         => $row[26] ?? null,

            // Data Ibu
            'nama_ibu'           => $row[27] ?? null,
            'nik_ibu'            => $row[28] ?? null,
            'tempat_lahir_ibu'   => $row[29] ?? null,
            'tanggal_lahir_ibu'  => $this->transformDate($row[30] ?? null),
            'pekerjaan_ibu'      => $row[31] ?? null,
            'pendidikan_ibu'     => $row[32] ?? null,

            // Data Wali
            'nama_wali'          => $row[33] ?? null,
            'nik_wali'           => $row[34] ?? null,
            'pekerjaan_wali'     => $row[35] ?? null,
            'pendidikan_wali'    => $row[36] ?? null,
            'hubungan_wali'      => $row[37] ?? null,

            // Status Pendaftaran
            'status_masuk'       => $row[38] ?? 'Baru',
            'asal_pindahan'      => $row[40] ?? null,
            'tanggal_masuk'      => $this->transformDate($row[45] ?? null),
            'kelas_id'           => $kelasId,
            'status'             => 'Aktif',

            // Fisik & Minat
            'bb'                 => $row[41] ?? null,
            'tb'                 => $row[42] ?? null,
            'gol_darah'          => $row[43] ?? null,
            'cita_cita'          => $row[47] ?? null,
            'hobi'               => $row[48] ?? null,
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
