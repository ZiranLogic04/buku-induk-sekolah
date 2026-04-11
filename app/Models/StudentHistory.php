<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentHistory extends Model
{
    protected $fillable = [
        'student_id',
        'tahun_ajaran_id',
        'semester',
        'kelas_id',
        'status',
        'tahun_keluar',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
