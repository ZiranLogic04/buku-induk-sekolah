<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    protected $fillable = [
        'student_id',
        'tanggal',
        'jenis',
        'dari_ke',
        'keterangan',
        'kelas_id',
        'tahun_ajaran_id',
        'semester',
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
