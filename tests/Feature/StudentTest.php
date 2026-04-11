<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup initial data for testing
        $this->tahunAjaran = TahunAjaran::create([
            'nama' => '2023/2024',
            'aktif' => true
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'X-RPL-1',
            'tingkat' => '10',
            'tahun_ajaran_id' => $this->tahunAjaran->id
        ]);
    }

    /** @test */
    public function it_can_list_students()
    {
        Student::create([
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'status' => 'Aktif'
        ]);

        $response = $this->getJson('/api/admin/students');

        $response->assertStatus(200)
                 ->assertJsonStructure(['students', 'classes']);
    }

    /** @test */
    public function it_can_create_a_student()
    {
        $data = [
            'nama' => 'Ani Wijaya',
            'jenis_kelamin' => 'P',
            'kelas_id' => $this->kelas->id,
            'nis' => '12345',
            'nisn' => '0012345678'
        ];

        $response = $this->postJson('/api/admin/students', $data);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Siswa berhasil ditambahkan']);

        $this->assertDatabaseHas('students', [
            'nama' => 'Ani Wijaya',
            'nis' => '12345'
        ]);
    }

    /** @test */
    public function it_can_update_a_student()
    {
        $student = Student::create([
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'status' => 'Aktif'
        ]);

        $data = [
            'nama' => 'Budi Santoso Updated',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'status' => 'Pindah'
        ];

        $response = $this->putJson("/api/admin/students/{$student->id}", $data);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Data Siswa berhasil diperbarui']);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'nama' => 'Budi Santoso Updated',
            'status' => 'Pindah'
        ]);
    }

    /** @test */
    public function it_can_delete_a_student()
    {
        $student = Student::create([
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'status' => 'Aktif'
        ]);

        $response = $this->deleteJson("/api/admin/students/{$student->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Data Siswa berhasil dihapus']);

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    /** @test */
    public function it_can_bulk_move_students_to_new_class()
    {
        $student = Student::create([
            'nama' => 'Siswa Naik',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'status' => 'Aktif'
        ]);

        $targetKelas = Kelas::create([
            'nama' => 'XI-RPL-1',
            'tingkat' => '11',
            'tahun_ajaran_id' => $this->tahunAjaran->id
        ]);

        $data = [
            'target_type' => 'kelas',
            'target_kelas_id' => $targetKelas->id,
            'actions' => [
                [
                    'student_id' => $student->id,
                    'action' => 'Naik'
                ]
            ]
        ];

        $response = $this->postJson('/api/admin/students/bulk-move', $data);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'kelas_id' => $targetKelas->id,
            'status' => 'Aktif'
        ]);
    }

    /** @test */
    public function it_can_bulk_graduate_students()
    {
        $student = Student::create([
            'nama' => 'Siswa Lulus',
            'jenis_kelamin' => 'P',
            'kelas_id' => $this->kelas->id,
            'status' => 'Aktif'
        ]);

        $data = [
            'target_type' => 'lulus',
            'actions' => [
                [
                    'student_id' => $student->id,
                    'action' => 'Lulus'
                ]
            ]
        ];

        $response = $this->postJson('/api/admin/students/bulk-move', $data);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'status' => 'Lulus',
            'kelas_id' => null
        ]);
    }
}
