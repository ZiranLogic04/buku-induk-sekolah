<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'student_id',
        'tahun_ajaran_id',
        'semester',
        'kelas_id',
        'catatan',
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

    public function items()
    {
        return $this->hasMany(GradeItem::class);
    }
}
