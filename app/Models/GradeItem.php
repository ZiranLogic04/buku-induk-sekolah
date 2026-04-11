<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeItem extends Model
{
    protected $fillable = [
        'grade_id',
        'subject_id',
        'nilai_angka',
        'nilai_pengetahuan',
        'nilai_keterampilan',
        'nilai_huruf',
        'keterangan',
        'keterangan_pengetahuan',
        'keterangan_keterampilan',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
