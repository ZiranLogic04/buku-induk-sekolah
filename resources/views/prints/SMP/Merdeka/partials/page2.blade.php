{{-- PAGE 2: Meninggalkan Sekolah + Tinggi/Berat + Kesehatan (stitch4) --}}
<div class="a4-page page2">
    <div class="p1-content-indent p1-relative" style="padding-right:4cm">
        {{-- Section D --}}
        <div class="section-title p1-section-title p1-section-header">
            <span class="p1-section-letter">D.</span><span>MENINGGALKAN SEKOLAH</span>
        </div>
        <div class="p1-section-indent p1-label">
            <div class="p2-row-main"><span class="p1-w-num">19.</span><span class="p2-w-label-main">Tamat Belajar/Lulus</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p2-row-sub"><span></span><span class="p2-sub-item"><span>a.</span><span>Tahun</span></span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p2-row-sub"><span></span><span class="p2-sub-item"><span>b.</span><span>Nomor Ijazah/STTB</span></span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p2-row-sub"><span></span><span class="p2-sub-item"><span>c.</span><span>Melanjutkan ke sekolah</span></span><span class="colon-align">:</span><div class="dotted-line"></div></div>

            <div class="p2-row-main p2-mt-2"><span class="p1-w-num">20.</span><span class="p2-w-label-main">Pindah sekolah</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p2-row-sub"><span></span><span class="p2-sub-item"><span>a.</span><span>Tingkat/Kelas yang ditinggalkan</span></span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p2-row-sub"><span></span><span class="p2-sub-item"><span>b.</span><span>ke Sekolah</span></span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p2-row-sub"><span></span><span class="p2-sub-item"><span>c.</span><span>ke Tingkat</span></span><span class="colon-align">:</span><div class="dotted-line"></div></div>

            <div class="p2-row-main p2-mt-2"><span class="p1-w-num">21.</span><span class="p2-w-label-main">Keluar sekolah</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p2-row-sub"><span></span><span class="p2-sub-item"><span>a.</span><span>Alasan Keluar sekolah</span></span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p2-row-sub"><span></span><span class="p2-sub-item"><span>b.</span><span>Hari dan tanggal Keluar sekolah</span></span><span class="colon-align">:</span><div class="dotted-line"></div></div>
        </div>

        {{-- Section E --}}
        <div class="section-title p1-section-title p1-section-header" style="margin-top:1.5rem">
            <span class="p1-section-letter">E.</span><span>LAIN-LAIN</span>
        </div>
        <div class="p1-section-indent p1-label">
            <div style="display:flex;margin-bottom:0.5rem"><span style="width:1.5rem">1.</span><span>TINGGI DAN BERAT BADAN PESERTA DIDIK</span></div>

            @for ($t = 0; $t < 2; $t++)
            <table class="p2-table">
                <tr>
                    <th rowspan="2" style="width:2rem">No.</th>
                    <th rowspan="2" style="width:8rem">Aspek<br>yang dinilai</th>
                    @for ($i = 0; $i < 6; $i++)
                    <th colspan="2">Thn Pelajaran<br>........ / ........</th>
                    @endfor
                </tr>
                <tr>
                    @for ($i = 0; $i < 6; $i++)
                    <th style="width:7%">Sem<br>Ganjil</th><th style="width:7%">Sem<br>Genap</th>
                    @endfor
                </tr>
                <tr>
                    <td>1</td><td style="text-align:left;padding:0 0.5rem">Tinggi Badan</td>
                    @for ($i = 0; $i < 12; $i++)<td>{!! $i === 0 && $student->tb ? $student->tb . ' Cm' : '..... Cm' !!}</td>@endfor
                </tr>
                <tr>
                    <td>2</td><td style="text-align:left;padding:0 0.5rem">Berat Badan</td>
                    @for ($i = 0; $i < 12; $i++)<td>{!! $i === 0 && $student->bb ? $student->bb . ' Kg' : '..... Kg' !!}</td>@endfor
                </tr>
            </table>
            @endfor

            <div style="display:flex;margin-top:1.5rem;margin-bottom:0.5rem"><span style="width:1.5rem">2.</span><span>KONDISI KESEHATAN</span></div>

            @for ($t = 0; $t < 2; $t++)
            <table class="p2-table">
                <tr>
                    <th rowspan="2" style="width:2rem">No.</th>
                    <th rowspan="2" style="width:8rem">Aspek<br>Kesehatan</th>
                    @for ($i = 0; $i < 6; $i++)
                    <th style="width:12%">Thn Pelajaran<br>........ / ........</th>
                    @endfor
                </tr>
                <tr>
                    @for ($i = 0; $i < 6; $i++)
                    <th>Keterangan</th>
                    @endfor
                </tr>
                @php $kesehatan = ['Pendengaran', 'Penglihatan', 'Gigi', 'Penyakit Lain']; @endphp
                @foreach ($kesehatan as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}.</td>
                    <td style="text-align:left;padding:0 0.5rem">{{ $item }}</td>
                    @for ($i = 0; $i < 6; $i++)
                        <td>{!! ($i === 0 && $item === 'Penyakit Lain' && $student->penyakit) ? \Illuminate\Support\Str::limit($student->penyakit, 18, '..') : '.........................' !!}</td>
                    @endfor
                </tr>
                @endforeach
            </table>
            @endfor
        </div>
    </div>
</div>
