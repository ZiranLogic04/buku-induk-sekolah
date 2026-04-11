<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = [
        'name',
        'type',
        'status',
        'npsn',
        'nss',
        'address',
        'level',
        'headmaster',
        'headmaster_nip',
        'academic_year',
        'semester',
        'config',
        'logo_path',
    ];

    protected $casts = [
        'config' => 'array',
    ];
}
