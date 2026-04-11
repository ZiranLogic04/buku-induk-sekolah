<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lembaga;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Hash;

class RealDataSeeder extends Seeder
{
    public function run()
    {
        // 0. Clean up operator users as requested
        User::where('role', '!=', 'admin')->delete();

        // 1. Lembaga
        Lembaga::updateOrCreate(
            ['nama' => 'SMP Negeri 1 Harapan'],
            [
                'alamat' => 'Jl. Pendidikan No. 123, Kota Harapan',
                'nama_kepala_sekolah' => 'Dr. H. Ahmad Fauzi, M.Pd.',
                'nip_kepala_sekolah' => '197508122003121002',
                'nss' => '201026001001',
                'npsn' => '20202020',
                'jenjang' => 'SMP / MTs',
                'kurikulum' => 'Kurikulum Merdeka',
                'status' => 'Negeri'
            ]
        );

        // 2. Tahun Ajaran
        TahunAjaran::updateOrCreate(['nama' => '2023/2024'], ['aktif' => false]);
        $taAktif = TahunAjaran::updateOrCreate(['nama' => '2024/2025'], ['aktif' => true]);

        // 3. Jurusan
        $jurusan = Jurusan::updateOrCreate(['nama' => 'Umum']);

        // 4. Guru (5 Teachers)
        $gurus = [];
        $guruData = [
            ['nama' => 'Budi Santoso, S.Pd.', 'email' => 'budisantoso@guru.com', 'nip' => '198001012010011001', 'jk' => 'L'],
            ['nama' => 'Siti Aminah, M.Pd.', 'email' => 'sitiaminah@guru.com', 'nip' => '198505052015012002', 'jk' => 'P'],
            ['nama' => 'Iwan Fals, S.T.', 'email' => 'iwan@guru.com', 'nip' => '199001012020011003', 'jk' => 'L'],
            ['nama' => 'Ani Yudhoyono, M.Pd.', 'email' => 'ani@guru.com', 'nip' => '198205052012012004', 'jk' => 'P'],
            ['nama' => 'Dedi Corbuzier, S.Or.', 'email' => 'dedi@guru.com', 'nip' => '198801012018011005', 'jk' => 'L'],
        ];

        foreach ($guruData as $data) {
            $gurus[] = Guru::updateOrCreate(
                ['email' => $data['email']],
                [
                    'nama' => $data['nama'],
                    'nip' => $data['nip'],
                    'jenis_kelamin' => $data['jk'],
                    'password' => Hash::make('password'),
                    'password_text' => 'password'
                ]
            );
        }

        // 5. Kelas (5 Classes)
        $classes = [];
        $classNames = ['VII-A', 'VII-B', 'VIII-A', 'VIII-B', 'IX-A'];
        $tingkats = ['7', '7', '8', '8', '9'];

        foreach ($classNames as $idx => $name) {
            $classes[] = Kelas::updateOrCreate(
                ['nama' => $name, 'tahun_ajaran_id' => $taAktif->id],
                [
                    'jurusan_id' => $jurusan->id, 
                    'wali_kelas_id' => $gurus[$idx]->id, 
                    'tingkat' => $tingkats[$idx]
                ]
            );
        }

        // 6. Subjects (5+ Subjects)
        $subjects = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPA', 'IPS'];
        foreach ($subjects as $s) {
            Subject::updateOrCreate(['nama' => $s]);
        }

        // 7. Students (5 per class = 25 Students)
        $firstNames = ['Andi', 'Budi', 'Caca', 'Dedi', 'Euis', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko'];
        $lastNames = ['Pratama', 'Santoso', 'Lestari', 'Kusuma', 'Wijaya', 'Saputra', 'Putri', 'Hidayat', 'Permata', 'Ramadhan'];

        $sCount = 0;
        foreach ($classes as $class) {
            for ($i = 1; $i <= 5; $i++) {
                $sCount++;
                $fName = $firstNames[array_rand($firstNames)];
                $lName = $lastNames[array_rand($lastNames)];
                $fullName = $fName . ' ' . $lName . ' ' . $sCount;
                
                Student::updateOrCreate(
                    ['nis' => '100' . str_pad($sCount, 2, '0', STR_PAD_LEFT)],
                    [
                        'nama' => $fullName,
                        'nisn' => '00112233' . str_pad($sCount, 2, '0', STR_PAD_LEFT),
                        'jenis_kelamin' => ($i % 2 == 0) ? 'L' : 'P',
                        'kelas_id' => $class->id,
                        'status' => 'Aktif',
                        'tempat_lahir' => 'Kota Harapan',
                        'tanggal_lahir' => '2010-0' . (rand(1, 9)) . '-0' . (rand(1, 9)),
                        'alamat' => 'Jl. Mawar No. ' . $sCount,
                        'nama_ayah' => 'Ayah ' . $fName,
                        'nama_ibu' => 'Ibu ' . $fName,
                    ]
                );
            }
        }
    }
}
