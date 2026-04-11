<?php

namespace App\Imports;

use App\Models\Guru;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip if nama is empty
        if (empty($row['nama'])) {
            return null;
        }

        // Generate password if not provided
        $plainPassword = $row['password'] ?? Str::random(8);
        
        // Normalize gender
        $jk = $row['jenis_kelamin'] ?? 'Laki-laki';
        if (strtoupper($jk) === 'L' || strtolower($jk) === 'laki-laki' || strtolower($jk) === 'laki laki') {
            $jk = 'Laki-laki';
        } elseif (strtoupper($jk) === 'P' || strtolower($jk) === 'perempuan') {
            $jk = 'Perempuan';
        }

        return new Guru([
            'nama'                => $row['nama'],
            'nip'                 => $row['nip'] ?? null,
            'nik'                 => $row['nik'] ?? null,
            'jenis_kelamin'       => $jk,
            'tempat_lahir'        => $row['tempat_lahir'] ?? null,
            // Handle date format if necessary, assuming Y-m-d or reliable format from standard excel date
            'tanggal_lahir'       => $this->transformDate($row['tanggal_lahir'] ?? null),
            'nuptk'               => $row['nuptk'] ?? null,
            'alamat'              => $row['alamat'] ?? null,
            'no_hp'               => $row['no_hp'] ?? null,
            'email'               => $row['email'] ?? null,
            'pendidikan_terakhir' => $row['pendidikan_terakhir'] ?? null,
            'status_kepegawaian'  => $row['status_kepegawaian'] ?? 'Honorer',
            'tanggal_masuk'       => $this->transformDate($row['tanggal_masuk'] ?? null),
            'password'            => Hash::make($plainPassword),
            'password_text'       => Crypt::encryptString($plainPassword),
        ]);
    }

    private function transformDate($value)
    {
        if (!$value) return null;

        // If it's a numeric value (Excel date serial), transform it
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            } catch (\ErrorException | \Exception | \TypeError $e) {
                // Fallback to normal parsing if excel conversion fails
            }
        }

        // Otherwise assume it's a string date format (YYYY-MM-DD or similar)
        try {
            return date('Y-m-d', strtotime($value));
        } catch (\Exception $e) {
            return null;
        }
    }
}
