<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'tanggal',
        'kelas_id',
        'tahun_ajaran_id',
        'semester',
    ];

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
        return $this->hasMany(AttendanceItem::class);
    }
}
