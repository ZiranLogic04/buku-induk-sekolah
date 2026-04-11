<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Buku Induk Kelas - Template Belum Didesain</title>
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
    </style>
</head>
<body>
    @foreach($studentsData as $index => $data)
        @for ($i = 1; $i <= 3; $i++)
        <div class="a4-page">
            <h2>Template Buku Induk SD K13 (Cetak Sekelas)</h2>
            <p>Silakan buat desain laporan di file ini.</p>
            <p>Siswa: {{ $data['student']->nama }} ({{ $data['student']->nis }})</p>
            <p style="margin-top:2rem; font-size:0.8rem; color:gray;">Siswa {{ $index + 1 }} dari {{ count($studentsData) }} - Halaman {{ $i }}</p>
        </div>
        @endfor
    @endforeach
</body>
</html>