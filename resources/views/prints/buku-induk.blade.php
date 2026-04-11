<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Lembar Buku Induk Peserta Didik</title>
  <style>
    @page { size: A4; margin: 16mm 14mm; }
    * { box-sizing: border-box; }
    html, body {
      width: 210mm;
      min-height: 297mm;
    }
    body {
      font-family: "Times New Roman", serif;
      font-size: 12px;
      color: #111;
      margin: 0 auto;
    }
    @media screen {
      body { background: #f3f4f6; padding: 12mm 0; }
      .page { background: #fff; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 16mm 14mm; box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
    }
    .title {
      text-align: center;
      font-weight: bold;
      font-size: 15px;
      letter-spacing: 0.5px;
      margin-bottom: 10px;
      text-transform: uppercase;
    }
    .row {
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }
    .col {
      flex: 1;
      min-width: 0;
    }
    .photo-col {
      width: 55mm;
      flex: 0 0 55mm;
    }
    .box {
      border: 1px solid #000;
      padding: 6px;
      width: 30mm;
      height: 40mm;
      margin: 0 auto 4px auto;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      font-weight: bold;
      font-size: 12px;
    }
    .photo-note {
      font-size: 10px;
      margin-top: 6px;
      text-align: center;
    }
    .section-title {
      font-weight: bold;
      margin: 10px 0 4px;
      text-transform: uppercase;
    }
    .line-row {
      display: grid;
      grid-template-columns: 18px 1fr 12px 1fr;
      align-items: end;
      column-gap: 6px;
      margin: 2px 0;
    }
    .line-row.small {
      grid-template-columns: 24px 1fr 12px 1fr;
    }
    .line-row .label {
      white-space: nowrap;
    }
    .line-row .dots {
      border-bottom: 1px dotted #000;
      height: 10px;
    }
    .list {
      margin: 0;
      padding: 0;
    }
    .item {
      display: grid;
      grid-template-columns: 18px 1fr;
      column-gap: 6px;
      margin: 2px 0;
    }
    .subitem {
      display: grid;
      grid-template-columns: 26px 1fr;
      column-gap: 6px;
      margin: 2px 0;
    }
    .subitem .dots, .item .dots {
      border-bottom: 1px dotted #000;
      height: 10px;
    }
    .codes {
      display: grid;
      grid-template-columns: 1.1fr 1.1fr 0.4fr;
      column-gap: 12px;
      align-items: start;
      margin-bottom: 8px;
    }
    .code-block {
      display: grid;
      grid-template-columns: 170px 1fr;
      column-gap: 6px;
      margin: 3px 0;
      align-items: end;
    }
    .code-block .dots {
      border-bottom: 1px dotted #000;
      height: 10px;
    }
    .nomor-urut {
      border: 1px solid #000;
      width: 130px;
      height: 70px;
      text-align: center;
      font-weight: bold;
      font-size: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      gap: 4px;
    }
    .nomor-urut .box-inner {
      width: 90%;
      height: 32px;
      border: 1px solid #000;
    }
  </style>
</head>
<body>
  <div class="page">
  <div class="title">III. LEMBAR BUKU INDUK PESERTA DIDIK</div>

  <div class="codes">
    <div>
      <div class="code-block"><div class="label">NOMOR INDUK SISWA</div><div class="dots"></div></div>
      <div class="code-block"><div class="label">NOMOR INDUK SISWA NASIONAL</div><div class="dots"></div></div>
      <div class="code-block"><div class="label">NOMOR KODE SEKOLAH</div><div class="dots"></div></div>
    </div>
    <div>
      <div class="code-block"><div class="label">NOMOR KODE KECAMATAN</div><div class="dots"></div></div>
      <div class="code-block"><div class="label">NOMOR KODE KAB/KOTA</div><div class="dots"></div></div>
      <div class="code-block"><div class="label">NOMOR KODE PROVINSI</div><div class="dots"></div></div>
    </div>
    <div class="nomor-urut">
      NOMOR URUT
      <div class="box-inner"></div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="section-title">KETERANGAN SISWA</div>
      <div class="item"><div>1.</div><div>Nama Murid</div></div>
      <div class="subitem"><div>a.</div><div>Lengkap <span class="dots" style="display:inline-block;width:70%;"></span></div></div>
      <div class="subitem"><div>b.</div><div>Panggilan <span class="dots" style="display:inline-block;width:70%;"></span></div></div>
      <div class="item"><div>2.</div><div>Jenis Kelamin <span class="dots" style="display:inline-block;width:70%;"></span> Laki-laki / Perempuan *</div></div>
      <div class="item"><div>3.</div><div>Kelahiran</div></div>
      <div class="subitem"><div>a.</div><div>Tanggal lahir <span class="dots" style="display:inline-block;width:70%;"></span></div></div>
      <div class="subitem"><div>b.</div><div>Tempat lahir <span class="dots" style="display:inline-block;width:70%;"></span></div></div>
      <div class="item"><div>4.</div><div>Agama <span class="dots" style="display:inline-block;width:80%;"></span></div></div>
      <div class="item"><div>5.</div><div>Kewarganegaraan <span class="dots" style="display:inline-block;width:65%;"></span></div></div>
      <div class="item"><div>6.</div><div>Anak Ke <span class="dots" style="display:inline-block;width:80%;"></span></div></div>
      <div class="item"><div>7.</div><div>Jumlah Saudara</div></div>
      <div class="subitem"><div>a.</div><div>Kandung <span class="dots" style="display:inline-block;width:70%;"></span></div></div>
      <div class="subitem"><div>b.</div><div>Tiri <span class="dots" style="display:inline-block;width:70%;"></span></div></div>
      <div class="subitem"><div>c.</div><div>Angkat <span class="dots" style="display:inline-block;width:70%;"></span></div></div>
      <div class="item"><div>8.</div><div>Bahasa sehari-hari dirumah <span class="dots" style="display:inline-block;width:55%;"></span></div></div>
      <div class="item"><div>9.</div><div>Golongan Darah <span class="dots" style="display:inline-block;width:72%;"></span></div></div>
      <div class="item"><div>10.</div><div>Alamat saat diterima</div></div>
      <div class="subitem"><div>a.</div><div>RT / RW <span class="dots" style="display:inline-block;width:70%;"></span></div></div>
      <div class="subitem"><div>b.</div><div>Desa/Kelurahan <span class="dots" style="display:inline-block;width:60%;"></span></div></div>
      <div class="subitem"><div>c.</div><div>Kecamatan <span class="dots" style="display:inline-block;width:70%;"></span></div></div>
      <div class="subitem"><div>d.</div><div>Kabupaten / Kota <span class="dots" style="display:inline-block;width:60%;"></span></div></div>
      <div class="subitem"><div>e.</div><div>Provinsi <span class="dots" style="display:inline-block;width:75%;"></span></div></div>
      <div class="item"><div>11.</div><div>Kode Pos &amp; No. Tlp. <span class="dots" style="display:inline-block;width:60%;"></span></div></div>
      <div class="item"><div>12.</div><div>Bertempat tinggal pada <span class="dots" style="display:inline-block;width:62%;"></span></div></div>
      <div class="item"><div>13.</div><div>Jarak ke sekolah <span class="dots" style="display:inline-block;width:70%;"></span></div></div>
    </div>
    <div class="photo-col">
      <div class="box">Pas Photo<br/>Ukuran :<br/>3 X 4</div>
      <div class="photo-note">Cap tiga jari tengah kiri mengenai pas photo bagian bawah</div>
      <div style="height: 14px;"></div>
      <div class="box">Pas Photo<br/>Ukuran :<br/>3 X 4</div>
      <div class="photo-note">Cap tiga jari tengah kiri mengenai pas photo bagian bawah</div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="section-title">KETERANGAN ORANG TUA/WALI PESERTA DIDIK</div>
      <div class="item"><div>14.</div><div>Nama Orang tua Kandung</div></div>
      <div class="subitem"><div>a.</div><div>Ayah <span class="dots" style="display:inline-block;width:75%;"></span></div></div>
      <div class="subitem"><div>b.</div><div>Ibu <span class="dots" style="display:inline-block;width:75%;"></span></div></div>
      <div class="item"><div>15.</div><div>Pendidikan terakhir</div></div>
      <div class="subitem"><div>a.</div><div>Ayah <span class="dots" style="display:inline-block;width:75%;"></span></div></div>
      <div class="subitem"><div>b.</div><div>Ibu <span class="dots" style="display:inline-block;width:75%;"></span></div></div>
      <div class="item"><div>16.</div><div>Pekerjaan Orang tua</div></div>
      <div class="subitem"><div>a.</div><div>Ayah <span class="dots" style="display:inline-block;width:75%;"></span></div></div>
      <div class="subitem"><div>b.</div><div>Ibu <span class="dots" style="display:inline-block;width:75%;"></span></div></div>
      <div class="item"><div>17.</div><div>Wali Murid</div></div>
      <div class="subitem"><div>a.</div><div>Nama <span class="dots" style="display:inline-block;width:78%;"></span></div></div>
      <div class="subitem"><div>b.</div><div>Hubungan Keluarga <span class="dots" style="display:inline-block;width:60%;"></span></div></div>
      <div class="subitem"><div>c.</div><div>Pendidikan terakhir <span class="dots" style="display:inline-block;width:63%;"></span></div></div>
      <div class="subitem"><div>d.</div><div>Pekerjaan <span class="dots" style="display:inline-block;width:75%;"></span></div></div>
    </div>
    <div class="photo-col">
      <div class="box">Pas Photo<br/>Ukuran :<br/>3 X 4</div>
      <div class="photo-note">Cap tiga jari tengah kiri mengenai pas photo bagian bawah</div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="section-title">PERKEMBANGAN PESERTA DIDIK</div>
      <div class="item"><div>18.</div><div>Pendidikan sebelumnya</div></div>
      <div class="subitem"><div>a.</div><div>Masuk menjadi peserta didik baru :</div></div>
      <div class="subitem"><div></div><div>1) Asal Sekolah <span class="dots" style="display:inline-block;width:68%;"></span></div></div>
      <div class="subitem"><div></div><div>2) Nama Sekolah <span class="dots" style="display:inline-block;width:67%;"></span></div></div>
      <div class="subitem"><div></div><div>3) Tanggal dan Nomor Ijazah/STTB <span class="dots" style="display:inline-block;width:50%;"></span></div></div>
      <div class="subitem"><div></div><div>4) Lain-lain <span class="dots" style="display:inline-block;width:75%;"></span></div></div>
      <div class="subitem"><div>b.</div><div>Pindahan dari sekolah lain :</div></div>
      <div class="subitem"><div></div><div>1) Nama Sekolah asal <span class="dots" style="display:inline-block;width:62%;"></span></div></div>
      <div class="subitem"><div></div><div>2) Dari Tingkat <span class="dots" style="display:inline-block;width:72%;"></span></div></div>
      <div class="subitem"><div></div><div>3) Diterima Tanggal <span class="dots" style="display:inline-block;width:65%;"></span></div></div>
      <div class="subitem"><div></div><div>4) No. Surat Keterangan <span class="dots" style="display:inline-block;width:62%;"></span></div></div>
    </div>
    <div class="photo-col">
      <div class="box">Pas Photo<br/>Ukuran :<br/>3 X 4</div>
      <div class="photo-note">Cap tiga jari tengah kiri mengenai pas photo bagian bawah</div>
    </div>
  </div>
  </div>
</body>
</html>
