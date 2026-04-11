<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Siswa - {{ $kelas->nama }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            font-size: 10pt;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 2px 0;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .attendance-table th, .attendance-table td {
            border: 1.5px solid #000;
            padding: 6px 2px;
            text-align: center;
        }
        .attendance-table th {
            background-color: #e5e7eb;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
        }
        .student-name-col {
            width: 200px;
            text-align: left !important;
            padding-left: 8px !important;
        }
        .no-col { width: 30px; }
        .day-col { width: 22px; }
        .summary-col { width: 30px; background-color: #f3f4f6; font-weight: bold; font-size: 9pt; }
        
        .status-h { color: #10b981; } /* Not seen in B&W but for reference */
        .status-s { font-weight: bold; }
        .status-i { font-weight: bold; }
        .status-a { background-color: #fee2e2; }
        .status-l { color: #999; }

        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .footer table {
            width: 100%;
        }
        .footer td {
            width: 33%;
            text-align: center;
            vertical-align: top;
        }
        .sig-space {
            height: 60px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ABSENSI SISWA</h1>
        <h1>{{ $lembaga->nama ?? 'NAMA SEKOLAH' }}</h1>
        <p>{{ $lembaga->alamat ?? '' }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 100px;"><strong>Kelas</strong></td>
            <td>: {{ $kelas->nama }}</td>
            <td style="width: 120px;"><strong>Bulan</strong></td>
            <td>: {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}</td>
        </tr>
        <tr>
            <td><strong>Tahun Ajaran</strong></td>
            <td>: {{ $tahunAjaran->nama ?? '-' }}</td>
            <td><strong>Semester</strong></td>
            <td>: {{ $semester }}</td>
        </tr>
    </table>

    <table class="attendance-table">
        <thead>
            <tr>
                <th rowspan="2" class="no-col">No</th>
                <th rowspan="2" class="student-name-col">Nama Siswa</th>
                <th colspan="{{ count($days) }}">Tanggal</th>
                <th colspan="4">Rekap</th>
            </tr>
            <tr>
                @foreach($days as $day)
                    <th class="day-col">{{ ltrim($day, '0') }}</th>
                @endforeach
                <th class="summary-col">H</th>
                <th class="summary-col">S</th>
                <th class="summary-col">I</th>
                <th class="summary-col">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $idx => $student)
                @php
                    $h = $s = $i = $a = $l = 0;
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="student-name-col">{{ $student->nama }}</td>
                    @foreach($days as $day)
                        @php
                            $fullDate = $bulan . '-' . $day;
                            $status = $data[$student->id][$fullDate] ?? '-';
                            if($status == 'H') $h++;
                            if($status == 'S') $s++;
                            if($status == 'I') $i++;
                            if($status == 'A') $a++;
                            if($status == 'L') $l++;
                        @endphp
                        <td class="{{ strtolower($status) !== '-' ? 'status-'.strtolower($status) : '' }}">
                            {{ $status !== '-' ? $status : '' }}
                        </td>
                    @endforeach
                    <td class="summary-col">{{ $h }}</td>
                    <td class="summary-col">{{ $s }}</td>
                    <td class="summary-col">{{ $i }}</td>
                    <td class="summary-col">{{ $a }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table>
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala Sekolah<br><br>
                    <div class="sig-space"></div>
                    <strong>{{ $lembaga->nama_kepala_sekolah ?? '(................................)' }}</strong><br>
                    NIP. {{ $lembaga->nip_kepala_sekolah ?? '-' }}
                </td>
                <td></td>
                <td>
                    Kota Harapan, {{ now()->translatedFormat('d F Y') }}<br>
                    Wali Kelas<br><br>
                    <div class="sig-space"></div>
                    <strong>{{ $waliKelas->nama ?? '(................................)' }}</strong><br>
                    NIP. {{ $waliKelas->nip ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
