<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Buku Induk - {{ $student->nama }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; color: #000; background: white; margin: 0; padding: 0; }
        .a4-page {
            width: 210mm; height: 297mm; background: white;
            position: relative; overflow: hidden;
            page-break-after: always;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .a4-page:last-child { page-break-after: auto; }
        /* Tambahkan CSS layout SMP K13 di sini */
    </style>
</head>
<body>
    @for ($i = 1; $i <= 3; $i++)
    <div class="a4-page">
        <h2>Template Buku Induk SMP K13 Belum Didesain</h2>
        <p>Silakan buat desain HTML & CSS laporan di dalam file ini.</p>
        <p>Siswa: {{ $student->nama }} ({{ $student->nis }})</p>
        <p style="margin-top:2rem; font-size:0.8rem; color:gray;">Halaman {{ $i }}</p>
    </div>
    @endfor
</body>
</html>