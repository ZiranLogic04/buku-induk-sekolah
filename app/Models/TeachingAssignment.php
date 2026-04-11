<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingAssignment extends Model
{
    protected $fillable = [
        'kelas_id',
        'subject_id',
        'guru_id',
        'tahun_ajaran_id',
        'semester',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
