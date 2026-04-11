{{-- PAGE 1: Keterangan Siswa & Orang Tua (stitch3) --}}
<div class="a4-page page1">


    <header style="margin-bottom: 1rem;">
        <h1 class="p1-title">III. LEMBAR BUKU INDUK PESERTA DIDIK</h1>
        <div class="p1-header-info">
            <div class="p1-header-grid">
                <div class="p1-code-grid">
                    <span style="white-space:nowrap;padding-right:0.25rem">NOMOR INDUK SISWA</span>
                    <span class="colon-align">:</span>
                    <div class="dotted-line">{{ $student->nis ?? '' }}</div>
                    <span style="white-space:nowrap;padding-right:0.25rem">NOMOR INDUK SISWA NASIONAL</span>
                    <span class="colon-align">:</span>
                    <div class="dotted-line">{{ $student->nisn ?? '' }}</div>
                    <span style="white-space:nowrap;padding-right:0.25rem">NOMOR KODE SEKOLAH</span>
                    <span class="colon-align">:</span>
                    <div class="dotted-line">{{ $lembaga->npsn ?? '' }}</div>
                </div>
                <div class="p1-code-grid">
                    <span style="white-space:nowrap;padding-right:0.25rem">NOMOR KODE KECAMATAN</span>
                    <span class="colon-align">:</span>
                    <div class="dotted-line"></div>
                    <span style="white-space:nowrap;padding-right:0.25rem">NOMOR KODE KAB/KOTA</span>
                    <span class="colon-align">:</span>
                    <div class="dotted-line"></div>
                    <span style="white-space:nowrap;padding-right:0.25rem">NOMOR KODE PROVINSI</span>
                    <span class="colon-align">:</span>
                    <div class="dotted-line"></div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:flex-start;width:100%">
                    <div class="p1-nomor-urut">
                        <p class="p1-nomor-urut-title">NOMOR URUT</p>
                        <div style="height:2rem"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="p1-content-indent" style="padding-right:4cm">
        {{-- Section A --}}
        <div class="section-title p1-section-title p1-section-header">
            <span class="p1-section-letter">A.</span><span>KETERANGAN SISWA</span>
        </div>
        <div class="p1-section-indent p1-label">
            <div class="p1-flex"><span class="p1-w-num">1.</span><span class="p1-w-label-main">Nama Murid</span><span class="colon-align">:</span></div>
            <div class="p1-flex-ml8 p1-relative">
                <span class="p1-w-num">a.</span><span class="p1-w-label-sub">Lengkap</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->nama ?? '' }}</div>
                <div class="p1-photo-wrapper" style="top: 0; right: -4cm;">
                    <div class="p1-photo-box"><span style="font-size:10pt">Pas Photo</span><span style="font-size:10pt">Ukuran :</span><span style="font-size:12pt;font-weight:bold;margin-top:4px">3 X 4</span></div>
                    <p class="p1-photo-caption">Cap tiga jari tengah kiri mengenai pas photo bagian bawah</p>
                </div>
            </div>
            <div class="p1-flex-ml8"><span class="p1-w-num">b.</span><span class="p1-w-label-sub">Panggilan</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p1-flex"><span class="p1-w-num">2.</span><span class="p1-w-label-main">Jenis Kelamin</span><span class="colon-align">:</span>
                <span style="margin-left:0.25rem">
                    @if($student->jenis_kelamin === 'L')
                        Laki-laki / <s>Perempuan</s>
                    @elseif($student->jenis_kelamin === 'P')
                        <s>Laki-laki</s> / Perempuan
                    @else
                        Laki-laki / Perempuan
                    @endif
                    *)
                </span>
            </div>
            <div class="p1-flex"><span class="p1-w-num">3.</span><span class="p1-w-label-main">Kelahiran</span><span class="colon-align">:</span></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">a.</span><span class="p1-w-label-sub">Tanggal lahir</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->format('d-m-Y') : '' }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">b.</span><span class="p1-w-label-sub">Tempat lahir</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->tempat_lahir ?? '' }}</div></div>
            <div class="p1-flex"><span class="p1-w-num">4.</span><span class="p1-w-label-main">Agama</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->agama ?? '' }}</div></div>
            <div class="p1-flex"><span class="p1-w-num">5.</span><span class="p1-w-label-main">Kewarganegaraan</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p1-flex"><span class="p1-w-num">6.</span><span class="p1-w-label-main">Anak ke</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->anak_ke ?? '' }}</div></div>
            <div class="p1-flex"><span class="p1-w-num">7.</span><span class="p1-w-label-main">Jumlah Saudara</span><span class="colon-align">:</span></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">a.</span><span class="p1-w-label-sub">Kandung</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->jml_saudara ?? '' }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">b.</span><span class="p1-w-label-sub">Tiri</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">c.</span><span class="p1-w-label-sub">Angkat</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p1-flex"><span class="p1-w-num">8.</span><span class="p1-w-label-main">Bahasa sehari-hari dirumah</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p1-flex p1-relative">
                <span class="p1-w-num">9.</span><span class="p1-w-label-main">Golongan Darah</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->gol_darah ?? '' }}</div>
                <div class="p1-photo-wrapper" style="top: 0; right: -4cm;">
                    <div class="p1-photo-box"><span style="font-size:10pt">Pas Photo</span><span style="font-size:10pt">Ukuran :</span><span style="font-size:12pt;font-weight:bold;margin-top:4px">3 X 4</span></div>
                    <p class="p1-photo-caption">Cap tiga jari tengah kiri mengenai pas photo bagian bawah</p>
                </div>
            </div>
            @php
                $alamatRaw = $student->alamat ?? '';
                preg_match('/(?i)(?:desa|des\.|kelurahan|kel\.)\s*([^,]+)/', $alamatRaw, $mDesa);
                preg_match('/(?i)(?:kecamatan|kec\.)\s*([^,]+)/', $alamatRaw, $mKec);
                preg_match('/(?i)(?:kabupaten|kab\.|kota)\s*([^,]+)/', $alamatRaw, $mKab);
                preg_match('/(?i)(?:provinsi|prov\.)\s*([^,]+)/', $alamatRaw, $mProv);

                $desa = $mDesa[1] ?? '';
                $kec = $mKec[1] ?? '';
                $kab = $mKab[1] ?? '';
                $prov = $mProv[1] ?? '';

                $jalan = preg_replace('/(?i)\,?\s*(?:desa|des\.|kelurahan|kel\.|kecamatan|kec\.|kabupaten|kab\.|kota|provinsi|prov\.)\s*[^,]+/', '', $alamatRaw);
                $jalan = trim($jalan, " ,");

                if (!$desa && !$kec && !$kab && !$prov) {
                    $jalan = $alamatRaw;
                }

                $desaGabungan = trim($jalan . ', ' . $desa, ", ");
            @endphp
            <div class="p1-flex"><span class="p1-w-num">10.</span><span class="p1-w-label-main">Alamat saat diterima</span><span class="colon-align">:</span></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">a.</span><span class="p1-w-label-sub">RT / RW</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->rt ? 'RT '.$student->rt : '' }} {{ $student->rw ? 'RW '.$student->rw : '' }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">b.</span><span class="p1-w-label-sub">Desa/Kelurahan</span><span class="colon-align">:</span><div class="dotted-line">{{ $desaGabungan }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">c.</span><span class="p1-w-label-sub">Kecamatan</span><span class="colon-align">:</span><div class="dotted-line">{{ $kec }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">d.</span><span class="p1-w-label-sub">Kabupaten / Kota</span><span class="colon-align">:</span><div class="dotted-line">{{ $kab }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">e.</span><span class="p1-w-label-sub">Provinsi</span><span class="colon-align">:</span><div class="dotted-line">{{ $prov }}</div></div>
            <div class="p1-flex"><span class="p1-w-num">11.</span><span class="p1-w-label-main">Kode Pos &amp; No. Tlp.</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
            <div class="p1-flex"><span class="p1-w-num">12.</span><span class="p1-w-label-main">Bertempat tinggal pada</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->tinggal_bersama ?? '' }}</div></div>
            <div class="p1-flex"><span class="p1-w-num">13.</span><span class="p1-w-label-main">Jarak ke sekolah</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
        </div>

        {{-- Section B --}}
        <div class="section-title p1-section-title p1-section-header">
            <span class="p1-section-letter">B.</span><span>KETERANGAN ORANG TUA/WALI PESERTA DIDIK</span>
        </div>
        <div class="p1-section-indent p1-label">
            <div class="p1-flex"><span class="p1-w-num">14.</span><span class="p1-w-label-main">Nama Orang tua Kandung</span><span class="colon-align">:</span></div>
            <div class="p1-flex-ml8 p1-relative">
                <span class="p1-w-num">a.</span><span class="p1-w-label-sub">Ayah</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->nama_ayah ?? '' }}</div>
                <div class="p1-photo-wrapper" style="top: 0; right: -4cm;">
                    <div class="p1-photo-box"><span style="font-size:10pt">Pas Photo</span><span style="font-size:10pt">Ukuran :</span><span style="font-size:12pt;font-weight:bold;margin-top:4px">3 X 4</span></div>
                    <p class="p1-photo-caption">Cap tiga jari tengah kiri mengenai pas photo bagian bawah</p>
                </div>
            </div>
            <div class="p1-flex-ml8"><span class="p1-w-num">b.</span><span class="p1-w-label-sub">Ibu</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->nama_ibu ?? '' }}</div></div>
            <div class="p1-flex"><span class="p1-w-num">15.</span><span class="p1-w-label-main">Pendidikan terakhir</span><span class="colon-align">:</span></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">a.</span><span class="p1-w-label-sub">Ayah</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->pendidikan_ayah ?? '' }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">b.</span><span class="p1-w-label-sub">Ibu</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->pendidikan_ibu ?? '' }}</div></div>
            <div class="p1-flex"><span class="p1-w-num">16.</span><span class="p1-w-label-main">Pekerjaan Orang tua</span><span class="colon-align">:</span></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">a.</span><span class="p1-w-label-sub">Ayah</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->pekerjaan_ayah ?? '' }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">b.</span><span class="p1-w-label-sub">Ibu</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->pekerjaan_ibu ?? '' }}</div></div>
            <div class="p1-flex"><span class="p1-w-num">17.</span><span class="p1-w-label-main">Wali Murid</span><span class="colon-align">:</span></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">a.</span><span class="p1-w-label-sub">Nama</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->nama_wali ?? '' }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">b.</span><span class="p1-w-label-sub">Hubungan Keluarga</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->hubungan_wali ?? '' }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">c.</span><span class="p1-w-label-sub">Pendidikan terakhir</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->pendidikan_wali ?? '' }}</div></div>
            <div class="p1-flex-ml8"><span class="p1-w-num">d.</span><span class="p1-w-label-sub">Pekerjaan</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->pekerjaan_wali ?? '' }}</div></div>
        </div>

        {{-- Section C --}}
        <div class="p1-relative" style="width:100%">
            <div class="section-title p1-section-title p1-section-header">
                <span class="p1-section-letter">C.</span><span>PERKEMBANGAN PESERTA DIDIK</span>
            </div>
            <div class="p1-photo-wrapper" style="top: 2px; right: -4cm;">
                <div class="p1-photo-box"><span style="font-size:10pt">Pas Photo</span><span style="font-size:10pt">Ukuran :</span><span style="font-size:12pt;font-weight:bold;margin-top:4px">3 X 4</span></div>
                <p class="p1-photo-caption">Cap tiga jari tengah kiri mengenai pas photo bagian bawah</p>
            </div>
        </div>
        <div class="p1-section-indent p1-label">
            <div class="p1-flex"><span class="p1-w-num">18.</span><span class="p1-w-label-main">Pendidikan sebelumnya</span><span class="colon-align">:</span></div>
            <div class="p1-ml-sub">
                <div class="p1-flex"><span class="p1-w-num">a.</span><span style="flex-grow:1">Masuk menjadi peserta didik baru :</span></div>
                <div class="p1-ml-sub">
                    <div class="p1-flex"><span class="p1-w-num">1)</span><span class="p1-w-label-sub2">Asal Sekolah</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
                    <div class="p1-flex"><span class="p1-w-num">2)</span><span class="p1-w-label-sub2">Nama Sekolah</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->status_masuk === 'Baru' ? ($student->asal_pindahan ?? '') : '' }}</div></div>
                    <div class="p1-flex"><span class="p1-w-num">3)</span><span class="p1-w-label-sub2">Tanggal &amp; No. Ijazah/STTB</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
                </div>
                <div class="p1-flex" style="margin-top:2px"><span class="p1-w-num">b.</span><span style="flex-grow:1">Pindahan dari sekolah lain :</span></div>
                <div class="p1-ml-sub">
                    <div class="p1-flex"><span class="p1-w-num">1)</span><span class="p1-w-label-sub2">Nama Sekolah asal</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->status_masuk === 'Pindahan' ? ($student->asal_pindahan ?? '') : '' }}</div></div>
                    <div class="p1-flex"><span class="p1-w-num">2)</span><span class="p1-w-label-sub2">Dari Tingkat</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
                    <div class="p1-flex"><span class="p1-w-num">3)</span><span class="p1-w-label-sub2">Diterima Tanggal</span><span class="colon-align">:</span><div class="dotted-line">{{ $student->status_masuk === 'Pindahan' && $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->format('d-m-Y') : '' }}</div></div>
                    <div class="p1-flex"><span class="p1-w-num">4)</span><span class="p1-w-label-sub2">No. Surat Keterangan</span><span class="colon-align">:</span><div class="dotted-line"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>
