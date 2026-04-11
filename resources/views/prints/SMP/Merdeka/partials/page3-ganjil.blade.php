{{-- PAGE 3: Rapor Semester Ganjil (stitch5) --}}
<div class="a4-page page3">
    <header class="p3-header">
        <h1>LAPORAN HASIL CAPAIAN PEMBELAJARAN</h1>
        <h1>KURIKULUM MERDEKA SEKOLAH MENENGAH PERTAMA (SMP)</h1>
    </header>

    <div class="p3-info">
        <div class="p3-info-grid">
            <div class="p3-info-left">
                <span style="white-space:nowrap;width:130px;padding-right:0.25rem">Nama Peserta Didik</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->nama ?? '' }}</div>
                <span style="white-space:nowrap;width:130px;padding-right:0.25rem">NISN</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->nisn ?? '' }}</div>
                <span style="white-space:nowrap;width:130px;padding-right:0.25rem">Sekolah</span><span class="colon-align">:</span><div class="dotted-line">{{ $lembaga->nama ?? '' }}</div>
                <span style="white-space:nowrap;width:130px;padding-right:0.25rem">Alamat</span><span class="colon-align">:</span><div class="dotted-line">{{ $lembaga->alamat ?? '' }}</div>
            </div>
            <div class="p3-info-right">
                <span style="white-space:nowrap;width:120px;padding-right:0.25rem">Semester</span><span class="colon-align">:</span><span class="font-bold" style="margin-left:0.25rem">I (satu) / Ganjil</span>
                <span style="white-space:nowrap;width:120px;padding-right:0.25rem;margin-top:2px">Kelas</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->kelas->nama ?? '' }}</div>
                <span style="white-space:nowrap;width:120px;padding-right:0.25rem;margin-top:2px">Fase</span><span class="colon-align">:</span><div class="dotted-line"></div>
                <span style="white-space:nowrap;width:120px;padding-right:0.25rem;margin-top:2px">Tahun Pelajaran</span><span class="colon-align">:</span><div class="dotted-line">{{ $tahunAjaran->nama ?? '' }}</div>
            </div>
        </div>
    </div>

    {{-- A. INTRAKURIKULER --}}
    <div class="p3-section-title">A. INTRAKURIKULER</div>
    <table class="p3-table">
        <tr>
            <th style="width:2rem">NO</th>
            <th style="width:35%">MATA PELAJARAN</th>
            <th style="width:8%">NILAI<br>AKHIR</th>
            <th>CAPAIAN PEMBELAJARAN</th>
            <th style="width:8%">KKTP</th>
        </tr>
        <tr><td>1</td><td class="p3-text-left-pad">Pendidikan Agama ........................... dan Budi Pekerti</td><td>{{ $gradeMap['Pendidikan Agama dan Budi Pekerti']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Pendidikan Agama dan Budi Pekerti']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>2</td><td class="p3-text-left-pad">Pendidikan Pancasila</td><td>{{ $gradeMap['Pendidikan Pancasila']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Pendidikan Pancasila']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>3</td><td class="p3-text-left-pad">Bahasa Indonesia</td><td>{{ $gradeMap['Bahasa Indonesia']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Bahasa Indonesia']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>4</td><td class="p3-text-left-pad">Matematika</td><td>{{ $gradeMap['Matematika']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Matematika']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>5</td><td class="p3-text-left-pad">Ilmu Pengetahuan Alam</td><td>{{ $gradeMap['Ilmu Pengetahuan Alam']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Ilmu Pengetahuan Alam']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>6</td><td class="p3-text-left-pad">Ilmu Pengetahuan Sosial</td><td>{{ $gradeMap['Ilmu Pengetahuan Sosial']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Ilmu Pengetahuan Sosial']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>7</td><td class="p3-text-left-pad">Bahasa Inggris</td><td>{{ $gradeMap['Bahasa Inggris']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Bahasa Inggris']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>8</td><td class="p3-text-left-pad">Pendidikan Jasmani, Olahraga dan Kesehatan</td><td>{{ $gradeMap['Pendidikan Jasmani, Olahraga dan Kesehatan']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Pendidikan Jasmani, Olahraga dan Kesehatan']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>9</td><td class="p3-text-left-pad">Informatika</td><td>{{ $gradeMap['Informatika']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Informatika']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>10</td><td class="p3-text-left-pad font-bold">Seni</td><td></td><td style="text-align:left;padding-left:5px"></td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">a. Seni Musik</td><td>{{ $gradeMap['Seni Musik']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Seni Musik']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">b. Seni Rupa</td><td>{{ $gradeMap['Seni Rupa']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Seni Rupa']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">c. Seni Theater</td><td>{{ $gradeMap['Seni Theater']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Seni Theater']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">d. Seni Tari</td><td>{{ $gradeMap['Seni Tari']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Seni Tari']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>11</td><td class="p3-text-left-pad font-bold">Prakarya</td><td></td><td style="text-align:left;padding-left:5px"></td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">a. Budi Daya</td><td>{{ $gradeMap['Budi Daya']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Budi Daya']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">b. Kerajinan</td><td>{{ $gradeMap['Kerajinan']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Kerajinan']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">c. Pengolahan</td><td>{{ $gradeMap['Pengolahan']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Pengolahan']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">d. Rekayasa</td><td>{{ $gradeMap['Rekayasa']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Rekayasa']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>12</td><td class="p3-text-left-pad">Bimbingan dan Konseling</td><td>{{ $gradeMap['Bimbingan dan Konseling']->nilai_angka ?? '' }}</td><td style="text-align:left;padding-left:5px">{{ $gradeMap['Bimbingan dan Konseling']->keterangan ?? '' }}</td><td></td></tr>
        <tr><td>13</td><td class="p3-text-left-pad font-bold">Muatan Lokal</td><td></td><td style="text-align:left;padding-left:5px"></td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">a.</td><td></td><td style="text-align:left;padding-left:5px"></td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">b.</td><td></td><td style="text-align:left;padding-left:5px"></td><td></td></tr>
        <tr><td></td><td class="p3-text-left-pad" style="padding-left:12px !important">c.</td><td></td><td style="text-align:left;padding-left:5px"></td><td></td></tr>
        <tr><td colspan="2" class="font-bold p3-text-left-pad">Jumlah Nilai</td><td></td><td></td><td></td></tr>
        <tr><td colspan="2" class="font-bold p3-text-left-pad">Rata-rata</td><td></td><td></td><td></td></tr>
    </table>

    {{-- B. P5 --}}
    <div class="p3-section-title" style="margin-top:0">B. PROJEK PENGUATAN PROFIL PELAJAR PANCASILA (P5)</div>
    @for ($i = 1; $i <= 3; $i++)
    <div class="p3-tema-row @if($i===3) p3-tema-row-last @endif"><span style="white-space:nowrap">Tema Projek. {{ $i }} :</span><div class="p3-dotted-line-tema"></div></div>
    @endfor
    <table class="p3-table" style="margin-top:0;border-top:0">
        <tr><th style="width:2rem">No.</th><th style="width:25%">Dimensi</th><th style="width:20%">Elemen</th><th>Sub-Elemen</th><th style="width:15%">Target Pencapaian di akhir Fase</th></tr>
        <tr><td>1.</td><td class="p3-text-left-pad">Beriman, Bertakwa kepada Tuhan<br>Yang Maha Esa, dan Berakhlak<br>Mulia</td><td></td><td></td><td></td></tr>
        <tr><td>2.</td><td class="p3-text-left-pad">Berkebhinekaan Global</td><td></td><td></td><td></td></tr>
        <tr><td>3.</td><td class="p3-text-left-pad">Bergotong Royong</td><td></td><td></td><td></td></tr>
        <tr><td>4.</td><td class="p3-text-left-pad">Mandiri</td><td></td><td></td><td></td></tr>
        <tr><td>5.</td><td class="p3-text-left-pad">Bernalar Kritis</td><td></td><td></td><td></td></tr>
        <tr><td>6.</td><td class="p3-text-left-pad">Kreatif</td><td></td><td></td><td></td></tr>
    </table>

    {{-- C. EKSTRAKURIKULER --}}
    <div class="p3-section-title" style="margin-top:0">C. EKSTRAKURIKULER</div>
    <table class="p3-table">
        <tr><th style="width:2rem">No</th><th style="width:35%">KEGIATAN EKSTRAKURIKULER</th><th>KETERANGAN</th><th style="width:8%">NILAI</th></tr>
        <tr><td>1</td><td class="p3-text-left-pad">Praja Muda Karana (Pramuka)</td><td></td><td></td></tr>
        <tr><td>2</td><td class="p3-text-left-pad"></td><td></td><td></td></tr>
        <tr><td>3</td><td class="p3-text-left-pad"></td><td></td><td></td></tr>
        <tr><td>4</td><td class="p3-text-left-pad"></td><td></td><td></td></tr>
    </table>

    {{-- D. PRESTASI --}}
    <div class="p3-section-title" style="margin-top:0">D. PRESTASI</div>
    <table class="p3-table">
        <tr><th style="width:2rem">No</th><th style="width:35%">JENIS PRESTASI</th><th>KETERANGAN</th><th style="width:8%">NILAI</th></tr>
        <tr><td>1</td><td class="p3-text-left-pad"></td><td></td><td></td></tr>
        <tr><td>2</td><td class="p3-text-left-pad"></td><td></td><td></td></tr>
        <tr><td>3</td><td class="p3-text-left-pad"></td><td></td><td></td></tr>
    </table>

    {{-- Footer --}}
    <table class="p3-table" style="margin-top:0">
        <tr>
            <td rowspan="3" class="p3-footer-bg" style="width:20%">KETIDAKHADIRAN</td>
            <td style="width:15%;text-align:left;padding:0 0.5rem">1. Sakit</td>
            <td style="width:10%;text-align:center">{{ $attendanceSummary->sakit > 0 ? $attendanceSummary->sakit : '........' }} Hari</td>
            <td rowspan="3" style="width:25%;vertical-align:top;padding-top:1rem">
                <div style="text-align:center;font-weight:bold">Mengetahui<br>Kepala Sekolah</div>
                <div style="margin-top:5rem;text-align:left;padding:0 0.5rem;font-weight:bold">NIP. {{ $lembaga->nip_kepala_sekolah ?? '_____________________' }}</div>
            </td>
            <td rowspan="3" style="width:30%;vertical-align:top;padding-top:1rem">
                <div style="text-align:center;font-weight:bold;margin-top:1rem">Wali Kelas</div>
                <div style="margin-top:5rem;text-align:left;padding:0 0.5rem;font-weight:bold">NIP. {{ $waliKelas->nip ?? '_____________________' }}</div>
            </td>
        </tr>
        <tr>
            <td style="text-align:left !important;padding:0 0.5rem">2. Ijin</td>
            <td style="text-align:center">{{ $attendanceSummary->ijin > 0 ? $attendanceSummary->ijin : '........' }} Hari</td>
        </tr>
        <tr>
            <td style="text-align:left !important;padding:0 0.5rem">3. Tanpa Keterangan</td>
            <td style="text-align:center">{{ $attendanceSummary->alpha > 0 ? $attendanceSummary->alpha : '........' }} hari</td>
        </tr>
    </table>
</div>
