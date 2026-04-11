<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'nis', 'nisn', 'foto', 'nama', 'jenis_kelamin', 
        'tempat_lahir', 'tanggal_lahir', 'alamat', 
        'nama_ayah', 'nama_ibu', 'kelas_id', 'status',
        'tanggal_masuk', 'status_masuk', 'asal_pindahan',
        'tahun_masuk', 'tahun_keluar', 'last_generated_at', 'last_generated_filename',
        // Siswa Ekstra
        'agama', 'nkk', 'nik', 'anak_ke', 'jml_saudara', 'penyakit', 'rt', 'rw', 'tinggal_bersama', 'no_akte',
        // Ayah Ekstra
        'nik_ayah', 'tempat_lahir_ayah', 'tanggal_lahir_ayah', 'pekerjaan_ayah', 'pendidikan_ayah', 'penghasilan_ayah', 'no_hp_ayah',
        // Ibu Ekstra
        'nik_ibu', 'tempat_lahir_ibu', 'tanggal_lahir_ibu', 'pekerjaan_ibu', 'pendidikan_ibu',
        // Wali
        'nama_wali', 'nik_wali', 'pekerjaan_wali', 'pendidikan_wali', 'hubungan_wali',
        // Fisik & Minat
        'bb', 'tb', 'gol_darah', 'cita_cita', 'hobi',
        // Dokumen
        'dok_akte', 'dok_kk', 'dok_kip', 'dok_ktp_ortu'
    ];

    protected $casts = [
        'last_generated_at' => 'datetime',
        'tanggal_lahir_ayah' => 'date',
        'tanggal_lahir_ibu' => 'date',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function histories()
    {
        return $this->hasMany(StudentHistory::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function mutations()
    {
        return $this->hasMany(Mutation::class);
    }

    public function attendanceItems()
    {
        return $this->hasMany(AttendanceItem::class);
    }

    protected static function booted()
    {
        static::deleting(function ($student) {
            $student->histories()->delete();
            $student->grades()->delete();
            $student->mutations()->delete();
            $student->attendanceItems()->delete();
            // Delete alumni record if exists
            \App\Models\Alumni::where('student_id', $student->id)->delete();
        });
    }
}
