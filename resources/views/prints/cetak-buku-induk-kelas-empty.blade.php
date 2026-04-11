<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Buku Induk Kelas - Template Belum Tersedia</title>
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
        .message-box { text-align: center; border: 2px dashed #9ca3af; padding: 3rem; border-radius: 1rem; width: 80%; }
        .message-title { font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem; color: #374151; }
        .message-subtitle { font-size: 1.1rem; color: #4b5563; }
        .student-divider { padding: 1rem; background: #f3f4f6; color: #4b5563; width: 100%; text-align: center; font-weight: bold; border-bottom: 1px solid #d1d5db; position: absolute; top:0; left:0;}
    </style>
</head>
<body>
    @foreach($studentsData as $index => $data)
        @for ($i = 1; $i <= 3; $i++)
        <div class="a4-page">
            <div class="student-divider">Siswa: {{ $data['student']->nama ?? '-' }} ({{ $data['student']->nis ?? '-' }})</div>
            <div class="message-box">
                <h1 class="message-title">Template Belum Tersedia</h1>
                <p class="message-subtitle">Buku Induk untuk <strong>{{ $lembaga->jenjang ?? '-' }}</strong> dengan <strong>{{ $lembaga->kurikulum ?? '-' }}</strong> belum dibuat.</p>
                <p style="margin-top: 2rem; color: #6b7280; font-style: italic;">(Siswa {{ $index + 1 }} dari {{ count($studentsData) }} - Halaman {{ $i }} dari 3)</p>
            </div>
        </div>
        @endfor
    @endforeach
</body>
</html>
