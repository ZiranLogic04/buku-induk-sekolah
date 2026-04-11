<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Guru extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nik',
        'nip',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'nuptk',
        'alamat',
        'no_hp',
        'email',
        'pendidikan_terakhir',
        'status_kepegawaian',
        'tanggal_masuk',
        'password',
        'password_text',
    ];

    public function assignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    protected static function booted()
    {
        static::deleting(function ($guru) {
            $guru->assignments()->delete();
        });
    }
}
