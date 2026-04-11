<table>
    <thead>
        <tr>
            <th colspan="{{ 3 + $daysInMonth + 5 }}" style="text-align: center; font-weight: bold; font-size: 14pt;"> REKAPITULASI ABSENSI SISWA </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + $daysInMonth + 5 }}" style="text-align: center; font-weight: bold;"> {{ $kelas->nama ?? '' }} - BULAN {{ strtoupper($bulanText) }} </th>
        </tr>
        <tr></tr>
        <tr>
            <th rowspan="2" style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; vertical-align: middle; text-align: center;">No</th>
            <th rowspan="2" style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; vertical-align: middle; text-align: center;">Nama Siswa</th>
            <th rowspan="2" style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; vertical-align: middle; text-align: center;">NIS</th>
            <th colspan="{{ $daysInMonth }}" style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; text-align: center;">Tanggal</th>
            <th colspan="5" style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; text-align: center;">Rekap</th>
        </tr>
        <tr>
            @for($i = 1; $i <= $daysInMonth; $i++)
                <th style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; text-align: center;">{{ $i }}</th>
            @endfor
            <th style="border: 1px solid #000; background-color: #e2efda; font-weight: bold; text-align: center;">H</th>
            <th style="border: 1px solid #000; background-color: #fff2cc; font-weight: bold; text-align: center;">S</th>
            <th style="border: 1px solid #000; background-color: #ddebf7; font-weight: bold; text-align: center;">I</th>
            <th style="border: 1px solid #000; background-color: #fce4d6; font-weight: bold; text-align: center;">A</th>
            <th style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; text-align: center;">L</th>
        </tr>
    </thead>
    <tbody>
        @foreach($matrix as $index => $row)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000;">{{ $row['nama'] }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $row['nis'] }}</td>
                @for($i = 1; $i <= $daysInMonth; $i++)
                    <td style="border: 1px solid #000; text-align: center;">{{ $row['days'][$i] }}</td>
                @endfor
                <td style="border: 1px solid #000; text-align: center; background-color: #e2efda;">{{ $row['rekap']['H'] }}</td>
                <td style="border: 1px solid #000; text-align: center; background-color: #fff2cc;">{{ $row['rekap']['S'] }}</td>
                <td style="border: 1px solid #000; text-align: center; background-color: #ddebf7;">{{ $row['rekap']['I'] }}</td>
                <td style="border: 1px solid #000; text-align: center; background-color: #fce4d6;">{{ $row['rekap']['A'] }}</td>
                <td style="border: 1px solid #000; text-align: center; background-color: #f2f2f2;">{{ $row['rekap']['L'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
