# Project Status — Buku Induk Sekolah

Tanggal: 2026-03-21

## Tujuan Utama
Web untuk cetak Buku Induk. Data siswa terikat ke Tahun Ajaran & Semester. “Travel data” = login memilih tahun ajaran/semester agar data tahun lalu bisa ditampilkan ulang untuk cetak ulang.

## Keputusan Alur Penting
- Login memilih Tahun Ajaran + Semester (disimpan di session). Data halaman lain mengikuti konteks login.
- Siswa dibagi: aktif di menu Siswa, mutasi di menu Mutasi, lulus di menu Alumni.
- Mutasi & Alumni hanya tampil (read-only), bukan input manual.

## Yang Sudah Beres (Ringkas)
### Auth & Session
- Login email saja (guru email juga bisa, auto user operator).
- Session menyimpan `academic_year` + `semester`.
- CSRF token diperbarui setelah login untuk mencegah mismatch.

### Lembaga
- CRUD lembaga + upload/delete logo (limit 5MB).
- Tahun ajaran & jurusan bisa ditambah.
- Validasi jenjang disesuaikan dengan opsi UI.
- Tahun ajaran divalidasi exists.

### Kelas
- List kelas mengikuti tahun ajaran dari login (server-side).
- Jurusan “Umum” selalu tersedia dari backend.
- Tingkat otomatis sesuai jenjang (SD/MI: 1–6, SMP/MTs: 7–9, SMA/MA/SMK: 10–12).
- Jurusan default “Umum” saat tambah, tapi tidak dikunci (edit tetap dari DB).
- Kolom “Jumlah Siswa” ditampilkan (count siswa aktif per kelas).
- Modal tambah kelas: sudah disesuaikan dengan layout modern + teks tahun ajaran otomatis.
- Warning hapus kelas: ada peringatan dampak ke data lain.

### Mata Pelajaran
- UI sudah diubah gaya selaras dengan menu Kelas (header biru, table header biru, filter di atas tabel).
- Modal tambah mata pelajaran sudah ditata ulang gaya “soft” mirip contoh.

## Perubahan Teknis Penting
- Models & Controllers tambahan: StudentHistory, Grades, Mutations, Alumni, TeachingAssignments, Attendance.
- User management (admin only) sudah ada.
- Sidebar/header sudah disesuaikan (toggle, logout di header).

## Catatan & Hal yang Perlu Dicek Lanjutan
1. **Mata Pelajaran**: masih perlu review akhir (ukuran, ikon warna, spacing) sesuai contoh terbaru.
2. **Siswa Aktif vs Alumni/Mutasi**: cek konsistensi data saat status berubah.
3. **Guru password update**: ada bug lama di GuruController (hash masuk ke password_text) — belum diperbaiki.
4. **Integrasi data tahun ajaran**: pastikan semua menu konsisten mengikuti session login.

## Arah Selanjutnya (Saran)
- Finalisasi UI Mata Pelajaran (tabel + modal).
- Lanjut review menu Guru, Siswa, Nilai, Absen.
- Uji alur “travel data” (logout → login tahun ajaran lain → data berubah).

## Kredensial Seed
- Admin: admin@sekolah.local / admin123

