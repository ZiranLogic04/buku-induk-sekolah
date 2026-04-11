<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [

        'nama',
        'singkatan',
        'kelompok',
        'tingkat',
        'jurusan',
        'urutan',
    ];

    protected $casts = [
        'tingkat' => 'array',
    ];
}
