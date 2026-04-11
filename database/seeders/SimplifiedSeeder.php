<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jurusan;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SimplifiedSeeder extends Seeder
{
    public function run()
    {
        // Ensure Jurusan 'Umum' exists
        if (Jurusan::count() == 0) {
            Jurusan::create(['nama' => 'Umum']);
        }

        // Ensure Tahun Ajaran exists
        if (TahunAjaran::count() == 0) {
            TahunAjaran::create(['nama' => '2024/2025', 'aktif' => true]);
        }

        // Seed default admin user
        User::updateOrCreate(
            ['email' => 'admin@sekolah.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );
    }
}
