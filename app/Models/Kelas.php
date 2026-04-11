<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'nama',
        'tingkat',
        'jurusan_id',
        'tahun_ajaran_id',
        'wali_kelas_id',
        'ruangan',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
