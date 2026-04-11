<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembaga extends Model
{
    protected $table = 'lembaga';

    protected $fillable = [
        'nama',
        'jenis',
        'status',
        'npsn',
        'nss',
        'alamat',
        'jenjang',
        'kurikulum',
        'nama_kepala_sekolah',
        'nip_kepala_sekolah',
        'tahun_ajaran',
        'semester',
        'jurusan',
        'logo_path',
    ];
}
