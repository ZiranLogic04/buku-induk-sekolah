<template>
  <div class="students-page">
    <!-- Header -->
    <div class="page-header header-blue">
      <div class="header-content">
        <h1 class="page-title">Data Siswa</h1>
        <p class="page-subtitle">Manajemen data peserta didik di lingkungan lembaga.</p>
      </div>
      <div class="header-actions">
          <button class="btn-secondary header-btn" title="Import Data" @click="openImportModal">
            <span class="material-symbols-outlined icon">upload</span>
            Import
          </button>
          <button class="btn-primary header-btn" @click="openModal">
            <span class="material-symbols-outlined icon">person_add</span>
            Tambah Siswa
          </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="table-filters">
      <div class="filter-item search">
        <span class="material-symbols-outlined">search</span>
        <input v-model="searchQuery" type="text" placeholder="Cari NIS, NISN, atau Nama Siswa..." @input="scheduleSearch" />
      </div>
      <div class="filter-item">
        <span class="material-symbols-outlined">class</span>
        <select v-model="filterKelas" @change="fetchData">
            <option value="">Semua Kelas</option>
            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.nama }}</option>
        </select>
      </div>
      <div class="filter-meta">
        Menampilkan <strong>{{ totalStudents }}</strong> data
      </div>
    </div>

    <!-- Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>NIS</th>
              <th>NISN</th>
              <th>Nama</th>
              <th>TTL</th>
              <th>Umur</th>
              <th>Jenis Kelamin</th>
              <th>Kelas</th>
              <th>Status Masuk</th>
              <th class="text-right pr-6">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in students" :key="student.id" class="hover-row">
              <td class="td-left font-bold text-muted text-xs">{{ student.nis || '-' }}</td>
              <td class="td-left font-bold text-muted text-xs">{{ student.nisn || '-' }}</td>
              <td class="td-left">
                <div class="student-info">
                    <span class="student-name">{{ student.nama }}</span>
                </div>
              </td>
              <td class="td-left text-sm text-slate-600">{{ formatTtl(student) }}</td>
              <td class="td-left text-sm text-slate-600">{{ getAge(student.tanggal_lahir) }}</td>
              <td class="td-left text-sm text-slate-600">
                {{ student.jenis_kelamin === 'L' ? 'Laki-laki' : (student.jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
              </td>
              <td class="td-left font-semibold text-primary">
                {{ student.kelas ? student.kelas.nama : '-' }}
              </td>
               <td class="td-center">
                <span class="badge" :class="getStatusMasukBadgeClass(student.status_masuk)">
                  {{ student.status_masuk || '-' }}
                </span>
              </td>
              <td class="td-right text-right pr-6">
                <div class="action-group">
                  <button 
                    v-if="student.status === 'Aktif'"
                    class="btn-icon btn-amber" 
                    title="Mutasi Siswa" 
                    @click="openMutasiModal(student)"
                  >
                    <span class="material-symbols-outlined icon-sm">sync_alt</span>
                  </button>


                  <button class="btn-icon btn-amber" title="Edit" @click="editStudent(student)">
                    <span class="material-symbols-outlined icon-sm">edit</span>
                  </button>
                  <button class="btn-icon btn-red" title="Hapus" @click="deleteStudent(student.id)">
                    <span class="material-symbols-outlined icon-sm">delete</span>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="students.length === 0">
                <td colspan="9" class="empty-state">
                    <span class="material-symbols-outlined empty-icon">group_off</span>
                    <p>Belum ada data siswa ditemukan.</p>
                </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination-footer">
        <p class="showing-text">Menampilkan {{ totalStudents }} Data Siswa (Halaman {{ currentPage }})</p>
        <div class="pagination-controls">
            <button class="page-btn nav" @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"><span class="material-symbols-outlined">chevron_left</span></button>
            <button class="page-btn active">{{ currentPage }}</button>
            <button class="page-btn nav" @click="changePage(currentPage + 1)" :disabled="currentPage >= lastPage"><span class="material-symbols-outlined">chevron_right</span></button>
        </div>
      </div>
    </div>
    

    <!-- Modal Tambah/Edit Siswa (Stitch Style) -->
    <div v-if="showModal" class="modal-backdrop">
      <div class="student-modal">
        <header class="student-modal-header">
          <div class="modal-header-left">
            <div class="modal-icon">
              <span class="material-symbols-outlined">person_add</span>
            </div>
            <div>
              <h1 class="modal-title">{{ isEdit ? 'Edit Data Siswa' : 'Tambah Data Siswa' }}</h1>
              <p class="modal-subtitle">Lengkapi formulir registrasi peserta didik dengan benar</p>
            </div>
          </div>
          <button class="modal-close" @click="closeModal" aria-label="Tutup">
            <span class="material-symbols-outlined">close</span>
          </button>
        </header>

        <main class="flex-1 p-6 md:p-8 overflow-y-auto bg-slate-50 main-scrollable">
          <form @submit.prevent="submitForm" class="space-y-6">
            <section class="form-card">
              <div class="section-header">
                <div class="icon-box bg-indigo-50 text-indigo-600">
                  <span class="material-symbols-outlined text-xl">badge</span>
                </div>
                <div>
                  <h2 class="section-title">Identitas Utama & Sekolah</h2>
                  <p class="section-subtitle">Informasi Pokok Pendaftaran</p>
                </div>
              </div>
              <div class="grid-3-col">
                <div class="field photo-field" style="align-items: center; justify-content: center; border: 2px dashed #cbd5e1; border-radius: 1rem; padding: 1.5rem; cursor:pointer;" @click="triggerFotoUpload">
                  <div class="photo-preview" :class="{ empty: !fotoPreview }" style="margin:auto;">
                    <img v-if="fotoPreview" :src="fotoPreview" alt="Foto siswa" style="border-radius: 0.5rem;"/>
                    <span v-else class="material-symbols-outlined" style="font-size: 3rem; color: #94a3b8;">add_a_photo</span>
                  </div>
                  <span class="field-label" style="margin-top:0.5rem; text-align:center;">FOTO SISWA</span>
                  <input ref="fotoInput" class="hidden-input" type="file" accept="image/*" @change="handleFotoChange" style="display:none;" />
                </div>
                <div class="span-2">
                  <div class="grid-2 mb-4">
                    <div class="field span-2">
                      <label class="field-label">Nama Lengkap Siswa</label>
                      <input v-model="form.nama" class="input" placeholder="Masukkan nama lengkap" type="text" required />
                    </div>
                    <div class="field">
                      <label class="field-label">Jenis Kelamin</label>
                      <select v-model="form.jenis_kelamin" class="input" required>
                        <option value="">Pilih</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                      </select>
                    </div>
                    <div class="field">
                      <label class="field-label">Kelas</label>
                      <select v-model="form.kelas_id" class="input" required>
                        <option value="">Pilih Kelas</option>
                        <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.nama }}</option>
                      </select>
                    </div>
                  </div>
                  <div class="grid-3-col">
                    <div class="field">
                      <label class="field-label">NIS</label>
                      <input v-model="form.nis" class="input" placeholder="Nomor Induk Siswa" type="text" />
                    </div>
                    <div class="field">
                      <label class="field-label">NISN</label>
                      <input v-model="form.nisn" class="input" placeholder="NIS Nasional" type="text" />
                    </div>
                    <div class="field">
                      <label class="field-label">NIK Siswa</label>
                      <input v-model="form.nik" class="input" placeholder="NIK Sesuai KK" type="text" />
                    </div>
                    <div class="field" v-show="isEdit">
                      <label class="field-label">Agama</label>
                      <input v-model="form.agama" class="input" placeholder="Islam, Kristen, dll" type="text" />
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <div class="grid-2">
              <section class="form-card">
                <div class="section-header">
                  <div class="icon-box bg-emerald-50 text-emerald-600">
                    <span class="material-symbols-outlined text-xl">event_available</span>
                  </div>
                  <h2 class="section-title">Riwayat Masuk</h2>
                </div>
                <div class="grid-2">
                  <div class="field">
                    <label class="field-label">Tanggal Masuk</label>
                    <input v-model="form.tanggal_masuk" class="input" type="date" />
                  </div>
                  <div class="field">
                    <label class="field-label">Tahun Masuk</label>
                    <input v-model="form.tahun_masuk" class="input" placeholder="YYYY" type="text" />
                  </div>
                  <div class="field">
                    <label class="field-label">Status Masuk</label>
                    <select v-model="form.status_masuk" class="input">
                      <option value="Baru">Baru</option>
                      <option value="Pindahan">Pindahan</option>
                    </select>
                  </div>
                  <div class="field span-2" v-if="form.status_masuk === 'Pindahan'">
                    <label class="field-label">Asal Pindahan</label>
                    <input v-model="form.asal_pindahan" class="input" placeholder="Nama sekolah asal jika pindahan" type="text" />
                  </div>
                </div>
              </section>

              <section class="form-card">
                <div class="section-header">
                  <div class="icon-box bg-orange-50 text-orange-600">
                    <span class="material-symbols-outlined text-xl">cake</span>
                  </div>
                  <h2 class="section-title">Kelahiran</h2>
                </div>
                <div class="grid-2">
                  <div class="field span-2">
                    <label class="field-label">Tempat Lahir</label>
                    <input v-model="form.tempat_lahir" class="input" placeholder="Kota Lahir" type="text" />
                  </div>
                  <div class="field">
                    <label class="field-label">Tanggal Lahir</label>
                    <input v-model="form.tanggal_lahir" class="input" type="date" />
                  </div>
                  <div class="field" v-show="isEdit">
                    <label class="field-label">No. Akta Lahir</label>
                    <input v-model="form.no_akte" class="input" placeholder="Nomor Akta" type="text" />
                  </div>
                  <div class="field" v-show="isEdit">
                    <label class="field-label">Anak Ke-</label>
                    <input v-model="form.anak_ke" class="input" type="text" />
                  </div>
                  <div class="field" v-show="isEdit">
                    <label class="field-label">Jml Saudara</label>
                    <input v-model="form.jml_saudara" class="input" type="text" />
                  </div>
                </div>
              </section>
            </div>

            <section class="form-card">
              <div class="section-header">
                <div class="icon-box bg-blue-50 text-blue-600">
                  <span class="material-symbols-outlined text-xl">home</span>
                </div>
                <h2 class="section-title">Alamat & Domisili</h2>
              </div>
              <div class="grid-3-col">
                <div class="field span-2">
                  <label class="field-label">Alamat Domisili</label>
                  <textarea v-model="form.alamat" class="input h-16 textarea" placeholder="Alamat lengkap tempat tinggal sekarang..."></textarea>
                </div>
                <div class="grid-2">
                  <div class="field">
                    <label class="field-label">RT</label>
                    <input v-model="form.rt" class="input" placeholder="000" type="text" />
                  </div>
                  <div class="field">
                    <label class="field-label">RW</label>
                    <input v-model="form.rw" class="input" placeholder="000" type="text" />
                  </div>
                </div>
                <div class="field" v-show="isEdit">
                  <label class="field-label">Tinggal Bersama</label>
                  <input v-model="form.tinggal_bersama" class="input" placeholder="Orang Tua, Wali, Kost" type="text" />
                </div>
                <div class="field" v-show="isEdit">
                  <label class="field-label">No. KK (NKK)</label>
                  <input v-model="form.nkk" class="input" placeholder="Nomor Kartu Keluarga" type="text" />
                </div>
              </div>
            </section>

            <section class="form-card">
              <div class="section-header">
                <div class="icon-box bg-purple-50 text-purple-600">
                  <span class="material-symbols-outlined text-xl">family_restroom</span>
                </div>
                <h2 class="section-title">Data Keluarga & Wali</h2>
              </div>
              <div class="family-space">
                <div class="family-block father-block">
                  <h3 class="family-title father-title">Informasi Ayah</h3>
                  <div class="grid-3-col">
                    <div class="field span-2">
                      <label class="field-label">Nama Ayah Kandung</label>
                      <input v-model="form.nama_ayah" class="input" placeholder="Nama Lengkap" type="text" />
                    </div>
                    <div class="field" v-show="isEdit">
                      <label class="field-label">NIK Ayah</label>
                      <input v-model="form.nik_ayah" class="input" placeholder="NIK" type="text" />
                    </div>
                    <div class="field" v-show="isEdit">
                      <label class="field-label">No. HP Ayah</label>
                      <input v-model="form.no_hp_ayah" class="input" placeholder="08..." type="text" />
                    </div>
                    <div class="grid-2 span-2" v-show="isEdit">
                      <div class="field">
                        <label class="field-label">Tempat Lahir</label>
                        <input v-model="form.tempat_lahir_ayah" class="input" placeholder="Kota" type="text" />
                      </div>
                      <div class="field">
                        <label class="field-label">Tanggal Lahir</label>
                        <input v-model="form.tanggal_lahir_ayah" class="input" type="date" />
                      </div>
                    </div>
                    <div class="field" v-show="isEdit">
                      <label class="field-label">Pendidikan</label>
                      <input v-model="form.pendidikan_ayah" class="input" type="text" />
                    </div>
                    <div class="field" v-show="isEdit">
                      <label class="field-label">Pekerjaan</label>
                      <input v-model="form.pekerjaan_ayah" class="input" type="text" />
                    </div>
                    <div class="field" v-show="isEdit">
                      <label class="field-label">Penghasilan</label>
                      <input v-model="form.penghasilan_ayah" class="input" placeholder="Per Bulan" type="text" />
                    </div>
                  </div>
                </div>

                <div class="family-block mother-block">
                  <h3 class="family-title mother-title">Informasi Ibu</h3>
                  <div class="grid-3-col">
                    <div class="field span-2">
                      <label class="field-label">Nama Ibu Kandung</label>
                      <input v-model="form.nama_ibu" class="input" placeholder="Nama Lengkap" type="text" />
                    </div>
                    <div class="field" v-show="isEdit">
                      <label class="field-label">NIK Ibu</label>
                      <input v-model="form.nik_ibu" class="input" placeholder="NIK" type="text" />
                    </div>
                    <div class="grid-2 span-2" v-show="isEdit">
                      <div class="field">
                        <label class="field-label">Tempat Lahir</label>
                        <input v-model="form.tempat_lahir_ibu" class="input" placeholder="Kota" type="text" />
                      </div>
                      <div class="field">
                        <label class="field-label">Tanggal Lahir</label>
                        <input v-model="form.tanggal_lahir_ibu" class="input" type="date" />
                      </div>
                    </div>
                    <div class="field" v-show="isEdit">
                      <label class="field-label">Pendidikan</label>
                      <input v-model="form.pendidikan_ibu" class="input" type="text" />
                    </div>
                    <div class="field" v-show="isEdit">
                      <label class="field-label">Pekerjaan Ibu</label>
                      <input v-model="form.pekerjaan_ibu" class="input" type="text" />
                    </div>
                  </div>
                </div>

                <div class="family-block guardian-block" v-show="isEdit">
                  <h3 class="family-title guardian-title">Informasi Wali</h3>
                  <div class="grid-3-col">
                    <div class="field span-2">
                      <label class="field-label">Nama Wali</label>
                      <input v-model="form.nama_wali" class="input" placeholder="Nama lengkap wali" type="text" />
                    </div>
                    <div class="field">
                      <label class="field-label">NIK Wali</label>
                      <input v-model="form.nik_wali" class="input" type="text" />
                    </div>
                    <div class="field">
                      <label class="field-label">Hubungan Wali</label>
                      <input v-model="form.hubungan_wali" class="input" placeholder="Paman, Bibi, dll" type="text" />
                    </div>
                    <div class="field">
                      <label class="field-label">Pekerjaan Wali</label>
                      <input v-model="form.pekerjaan_wali" class="input" type="text" />
                    </div>
                    <div class="field">
                      <label class="field-label">Pendidikan Wali</label>
                      <input v-model="form.pendidikan_wali" class="input" type="text" />
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <section class="form-card" v-show="isEdit">
              <div class="section-header">
                <div class="icon-box bg-rose-50 text-rose-600">
                  <span class="material-symbols-outlined text-xl">monitor_weight</span>
                </div>
                <h2 class="section-title">Fisik & Bakat</h2>
              </div>
              <div class="grid-3-col">
                <div class="field">
                  <label class="field-label">Tinggi (cm)</label>
                  <input v-model="form.tb" class="input" type="text" />
                </div>
                <div class="field">
                  <label class="field-label">Berat (kg)</label>
                  <input v-model="form.bb" class="input" type="text" />
                </div>
                <div class="field">
                  <label class="field-label">Gol. Darah</label>
                  <select v-model="form.gol_darah" class="input">
                    <option value="">-</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="AB">AB</option>
                    <option value="O">O</option>
                  </select>
                </div>
                <div class="field span-2">
                  <label class="field-label">Penyakit Berat</label>
                  <input v-model="form.penyakit" class="input" placeholder="Riwayat medis yang butuh penanganan khusus" type="text" />
                </div>
                <div class="field">
                  <label class="field-label">Cita-cita</label>
                  <input v-model="form.cita_cita" class="input" type="text" />
                </div>
                <div class="field">
                  <label class="field-label">Hobi</label>
                  <input v-model="form.hobi" class="input" type="text" />
                </div>
              </div>
            </section>

          </form>
        </main>

        <footer class="single-modal-footer">
          <button class="btn-cancel" type="button" @click="closeModal">Batal</button>
          <button class="submit-btn" type="button" @click="submitForm" :disabled="form.processing">
            <span class="material-symbols-outlined">check_circle</span>
            Selesai Simpan
          </button>
        </footer>
      </div>
    </div>
    
     <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-backdrop">
      <div class="modal-panel modal-xs center-content">
        <div class="modal-body-center">
            <div class="danger-icon-box">
                 <span class="material-symbols-outlined icon-2xl">warning</span>
            </div>
            <h3 class="modal-heading-center">Hapus Data Siswa?</h3>
            <p class="modal-text-center">Aksi ini tidak dapat dibatalkan dan akan menghapus semua rekaman nilai yang terkait.</p>
        </div>
        <div class="modal-footer-center">
             <button class="btn-secondary" @click="closeDeleteModal">Batal</button>
             <button class="btn-danger" @click="confirmDelete">Hapus</button>
        </div>
      </div>
    </div>

    <!-- Import Modal -->
    <div v-if="showImportModal" class="modal-backdrop" @click.self="closeImportModal">
      <div class="import-modal">
        <header class="import-header">
          <div class="header-left">
            <div class="header-icon">
              <span class="material-symbols-outlined">person_add</span>
            </div>
            <div>
              <h2 class="modal-title">Import Data Siswa</h2>
              <p class="modal-subtitle">Unggah data siswa secara massal menggunakan template</p>
            </div>
          </div>
          <button class="modal-close" @click="closeImportModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </header>
        <main class="import-body">
          <div class="import-card">
            <div class="import-card-title">
              <span class="step-chip step-indigo">1</span>
              <h3>Langkah 1: Persiapkan Data</h3>
            </div>
            <p>Pastikan data siswa sudah sesuai dengan format yang ditentukan. Silakan unduh template Excel di bawah ini.</p>
            <button class="btn-download" type="button" @click="downloadTemplate">
              <span class="material-symbols-outlined">download</span>
              Download Template Excel
            </button>
          </div>

          <div class="import-card">
            <div class="import-card-title">
              <span class="step-chip step-indigo">2</span>
              <h3>Langkah 2: Upload File</h3>
            </div>
            <p>Upload file Excel yang sudah diisi data siswa.</p>
            <div class="dropzone" :class="{ 'dropzone-active': importFile }" @click="fileInput?.click()">
              <input type="file" ref="fileInput" @change="handleFileChange" accept=".xlsx, .xls, .csv" class="hidden-input" />
              <span class="material-symbols-outlined">upload_file</span>
              <div>
                <p class="dropzone-title">Klik atau seret file ke sini</p>
                <p class="dropzone-subtitle">format yang didukung: .xlsx, .xls, .csv (Maks. 5MB)</p>
              </div>
            </div>

            <div v-if="importFile" class="file-pill">
              <span class="material-symbols-outlined">check_circle</span>
              <div class="file-meta">
                <p class="file-name">{{ importFile.name }}</p>
                <p class="file-ready">File siap diimport</p>
              </div>
              <button class="file-remove" @click="clearImportFile" type="button">
                <span class="material-symbols-outlined">close</span>
              </button>
            </div>

            <div v-if="importError" class="import-error">
              <span class="material-symbols-outlined">error</span>
              <p>{{ importError }}</p>
            </div>
          </div>
        </main>
        <footer class="import-footer">
          <button class="btn-cancel" @click="closeImportModal">Batal</button>
          <button v-if="!importing" class="btn-primary-action" @click="submitImport" :disabled="!importFile">
            <span class="material-symbols-outlined">publish</span>
            Import
          </button>
          <button v-else class="btn-primary-action loading" disabled>
            <span class="material-symbols-outlined icon-spin">sync</span>
            Mengimport...
          </button>
        </footer>
      </div>
    </div>

    <!-- Mutasi Siswa Modal (Keluar) -->
    <div v-if="showMutasiModal" class="modal-backdrop" @click.self="closeMutasiModal">
      <div class="mutasi-modal">
        <header class="mutasi-header">
          <div class="mutasi-header-left">
            <div class="mutasi-header-icon">
              <span class="material-symbols-outlined">logout</span>
            </div>
            <div>
              <h3 class="mutasi-title">Mutasi Siswa (Keluar)</h3>
              <p class="mutasi-subtitle">Lengkapi formulir untuk memproses pengeluaran siswa</p>
            </div>
          </div>
          <button class="mutasi-close" @click="closeMutasiModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </header>

        <div class="mutasi-info-bar">
          <div class="mutasi-info">
            <div class="mutasi-info-icon">
              <span class="material-symbols-outlined">person</span>
            </div>
            <div>
              <p class="mutasi-info-label">Informasi Siswa</p>
              <p class="mutasi-info-name">
                {{ mutasiStudent?.nama || '-' }}
                <span class="mutasi-sep">|</span>
                <span class="mutasi-info-nis">{{ mutasiStudent?.nis || '-' }}</span>
              </p>
            </div>
          </div>
          <div class="mutasi-info-right">
            <p class="mutasi-info-label">Kelas Saat Ini</p>
            <p class="mutasi-info-class">{{ mutasiStudent?.kelas?.nama || '-' }}</p>
          </div>
        </div>

        <main class="mutasi-body">
          <form class="mutasi-form">
            <div class="mutasi-type-header">
              <p class="mutasi-type-title">Pilih Jenis Mutasi</p>
              <p class="mutasi-type-subtitle">Pilih alasan siswa keluar dari sekolah</p>
            </div>

            <div class="mutasi-cards">
              <label class="mutasi-card" :class="{ active: mutasiForm.jenis === 'Pindah' }">
                <input v-model="mutasiForm.jenis" class="hidden-input" type="radio" value="Pindah" />
                <div class="mutasi-card-icon">
                  <span class="material-symbols-outlined">local_shipping</span>
                </div>
                <span class="mutasi-card-title">Pindah</span>
                <span class="mutasi-card-subtitle">Pindah ke sekolah lain</span>
                <div class="mutasi-check">
                  <span class="material-symbols-outlined">check</span>
                </div>
              </label>

              <label class="mutasi-card" :class="{ active: mutasiForm.jenis === 'Mengundurkan Diri' }">
                <input v-model="mutasiForm.jenis" class="hidden-input" type="radio" value="Mengundurkan Diri" />
                <div class="mutasi-card-icon">
                  <span class="material-symbols-outlined">person_remove</span>
                </div>
                <span class="mutasi-card-title">Mengundurkan Diri</span>
                <span class="mutasi-card-subtitle">Berhenti atas kemauan sendiri</span>
                <div class="mutasi-check">
                  <span class="material-symbols-outlined">check</span>
                </div>
              </label>

              <label class="mutasi-card" :class="{ active: mutasiForm.jenis === 'Dikeluarkan' }">
                <input v-model="mutasiForm.jenis" class="hidden-input" type="radio" value="Dikeluarkan" />
                <div class="mutasi-card-icon">
                  <span class="material-symbols-outlined">gavel</span>
                </div>
                <span class="mutasi-card-title">Dikeluarkan</span>
                <span class="mutasi-card-subtitle">Tindakan indisipliner</span>
                <div class="mutasi-check">
                  <span class="material-symbols-outlined">check</span>
                </div>
              </label>
            </div>

            <div v-if="mutasiForm.jenis" class="mutasi-details">
              <div class="mutasi-grid">
                <div class="field">
                  <label class="field-label">
                    <span class="material-symbols-outlined label-icon highlight">calendar_today</span>
                    Tanggal Keluar <span class="required-star">*</span>
                  </label>
                  <input v-model="mutasiForm.tanggal" class="input" type="date" />
                </div>
                <div class="field">
                  <label class="field-label">
                    <span class="material-symbols-outlined label-icon highlight">school</span>
                    Nama Sekolah Tujuan / Alasan <span class="required-star">*</span>
                  </label>
                  <input v-model="mutasiForm.dari_ke" class="input" placeholder="Misal: SMA Negeri 1 Jakarta" type="text" />
                </div>
              </div>
            </div>
          </form>
        </main>

        <footer class="mutasi-footer">
          <p class="mutasi-footnote">* Pastikan semua data sudah sesuai sebelum menyimpan.</p>
          <div class="mutasi-actions">
            <button class="btn-cancel" type="button" @click="closeMutasiModal">Batal</button>
            <button class="btn-primary-action" type="button" @click="submitMutasi" :disabled="mutasiForm.loading">
              <span class="material-symbols-outlined">check_circle</span>
              Simpan Mutasi
            </button>
          </div>
        </footer>
      </div>
    </div>




    <Toast :show="toast.show" :type="toast.type" :title="toast.title" :message="toast.message" @close="toast.show = false" />

  </div>
</template>

<script setup>
import Toast from '@/Components/Toast.vue';
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

const students = ref([]);
const classes = ref([]);
const tahunAjarans = ref([]);
const searchQuery = ref('');
const filterKelas = ref('');

const currentPage = ref(1);
const lastPage = ref(1);
const totalStudents = ref(0);
let searchTimer = null;

const showModal = ref(false);
const showDeleteModal = ref(false);
const showImportModal = ref(false);
const showMutasiModal = ref(false);
const isEdit = ref(false);
const editId = ref(null);
const deleteId = ref(null);
const mutasiStudent = ref(null);
const importFile = ref(null);
const importing = ref(false);
const importError = ref(null);
const fileInput = ref(null);
const currentStep = ref(1);
const fotoInput = ref(null);
const fotoPreview = ref('');
let fotoObjectUrl = null;
const mutasiForm = reactive({
    jenis: '',
    tanggal: '',
    dari_ke: '',
    loading: false
});


const totalSteps = computed(() => isEdit.value ? 6 : 3);

const form = reactive({
    nis: '', nisn: '', foto: null, nama: '', jenis_kelamin: '', tempat_lahir: '', tanggal_lahir: '', alamat: '',
    nama_ayah: '', nama_ibu: '', kelas_id: '', tanggal_masuk: '', status_masuk: 'Baru', asal_pindahan: '',
    // Data Ekstra
    agama: '', nkk: '', nik: '', anak_ke: '', jml_saudara: '', penyakit: '', rt: '', rw: '', tinggal_bersama: '', no_akte: '',
    nik_ayah: '', tempat_lahir_ayah: '', tanggal_lahir_ayah: '', pekerjaan_ayah: '', pendidikan_ayah: '', penghasilan_ayah: '', no_hp_ayah: '',
    nik_ibu: '', tempat_lahir_ibu: '', tanggal_lahir_ibu: '', pekerjaan_ibu: '', pendidikan_ibu: '',
    nama_wali: '', nik_wali: '', pekerjaan_wali: '', pendidikan_wali: '', hubungan_wali: '',
    bb: '', tb: '', gol_darah: '', cita_cita: '', hobi: '',
    dok_akte: '', dok_kk: '', dok_kip: '', dok_ktp_ortu: ''
});

const errors = ref({});
const toast = reactive({ show: false, type: 'success', title: '', message: '' });

const showToast = (type, title, message) => {
    toast.type = type;
    toast.title = title;
    toast.message = message;
    toast.show = true;
};

const fetchData = async (page = 1) => {
    try {
        const response = await axios.get('/api/admin/students', {
            params: {
                page: page,
                search: searchQuery.value,
                kelas_id: filterKelas.value
            }
        });
        students.value = response.data.students.data;
        currentPage.value = response.data.students.current_page;
        lastPage.value = response.data.students.last_page;
        totalStudents.value = response.data.students.total || students.value.length;
        classes.value = response.data.classes;
        if (response.data.tahun_ajarans) {
            tahunAjarans.value = response.data.tahun_ajarans;
        }
    } catch (error) {
        showToast('error', 'Error', 'Gagal memuat data siswa.');
    }
};

const scheduleSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchData(1), 350);
};

const changePage = (page) => {
    if (page >= 1 && page <= lastPage.value) {
        fetchData(page);
    }
}

const goToNaikKelas = () => {
    router.push('/admin/students/upgrade');
};

const openModal = () => {
    isEdit.value = false;
    Object.keys(form).forEach(k => form[k] = '');
    form.status_masuk = 'Baru';
    form.foto = null;
    fotoPreview.value = '';
    if (fotoObjectUrl) {
        URL.revokeObjectURL(fotoObjectUrl);
        fotoObjectUrl = null;
    }
    errors.value = {};
    currentStep.value = 1;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    currentStep.value = 1;
    if (fotoObjectUrl) {
        URL.revokeObjectURL(fotoObjectUrl);
        fotoObjectUrl = null;
    }
};
const closeDeleteModal = () => { showDeleteModal.value = false; deleteId.value = null; };

const editStudent = (item) => {
    isEdit.value = true;
    editId.value = item.id;
    Object.keys(form).forEach(k => form[k] = item[k] || '');
    form.foto = null;
    form.status_masuk = item.status_masuk || 'Baru';
    fotoPreview.value = getFotoUrl(item?.foto);
    if (fotoObjectUrl) {
        URL.revokeObjectURL(fotoObjectUrl);
        fotoObjectUrl = null;
    }
    errors.value = {};
    currentStep.value = 1;
    showModal.value = true;
};

const nextStep = () => {
    if (currentStep.value < totalSteps.value) currentStep.value += 1;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value -= 1;
};

const submitForm = async () => {
    errors.value = {};
    try {
        const formData = new FormData();
        Object.entries(form).forEach(([key, value]) => {
            if (key === 'foto') {
                if (value instanceof File) formData.append('foto', value);
                return;
            }
            formData.append(key, value ?? '');
        });

        if (isEdit.value) {
            formData.append('_method', 'PUT');
            formData.append('status', 'Aktif');
            await axios.post(`/api/admin/students/${editId.value}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            showToast('success', 'Berhasil', 'Data siswa diperbarui');
        } else {
            await axios.post('/api/admin/students', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            showToast('success', 'Berhasil', 'Siswa ditambahkan');
        }
        closeModal();
        fetchData(currentPage.value);
    } catch (e) {
        if (e.response && e.response.status === 422) {
            errors.value = e.response.data.errors;
            showToast('error', 'Validasi Gagal', 'Silahkan periksa inputan anda.');
        } else {
            console.error(e);
            showToast('error', 'Gagal', 'Terjadi kesalahan sistem. Silahkan coba lagi.');
        }
    }
};

const deleteStudent = (id) => { deleteId.value = id; showDeleteModal.value = true; };

const confirmDelete = async () => {
    try {
        await axios.delete(`/api/admin/students/${deleteId.value}`);
        showToast('success', 'Terhapus', 'Data siswa dihapus');
        fetchData(currentPage.value);
        closeDeleteModal();
    } catch (e) {
        showToast('error', 'Gagal', 'Gagal menghapus siswa');
    }
};

const getInitials = (name) => name ? String(name).substring(0, 2).toUpperCase() : '??';

const getAvatarColorClass = (id) => {
    const classes = ['bg-blue', 'bg-purple', 'bg-amber', 'bg-emerald', 'bg-rose'];
    return classes[id % classes.length];
};

const openImportModal = () => {
    importFile.value = null;
    importError.value = null;
    if (fileInput.value) fileInput.value.value = null;
    showImportModal.value = true;
};

const closeImportModal = () => {
    if (!importing.value) {
        showImportModal.value = false;
        importFile.value = null;
        importError.value = null;
        if (fileInput.value) fileInput.value.value = null;
    }
};

const handleFileChange = (event) => {
    importFile.value = event.target.files && event.target.files[0] ? event.target.files[0] : null;
    importError.value = null;
};

const clearImportFile = () => {
    importFile.value = null;
    if (fileInput.value) fileInput.value.value = null;
};

const downloadTemplate = () => {
    window.open('/api/admin/students/template', '_blank');
};

const submitImport = async () => {
    if (!importFile.value) {
        importError.value = 'Silahkan pilih file terlebih dahulu.';
        return;
    }
    
    importing.value = true;
    const formData = new FormData();
    formData.append('file', importFile.value);

    try {
        await axios.post('/api/admin/students/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        showToast('success', 'Berhasil', 'Data siswa berhasil diimport.');
        fetchData();
        showImportModal.value = false;
    } catch (e) {
        const msg = e.response?.data?.message || 'Terjadi kesalahan saat import.';
        importError.value = msg;
        showToast('error', 'Gagal Import', msg);
    } finally {
        importing.value = false;
    }
};

const getStatusBadgeClass = (s) => {
    switch(s) {
        case 'Aktif': return 'badge-active';
        case 'Lulus': return 'badge-success';
        case 'Pindah': return 'badge-warning';
        case 'Keluar': return 'badge-danger';
        case 'Cuti': return 'badge-inactive';
        case 'Dikeluarkan': return 'badge-danger';
        case 'Mengundurkan Diri': return 'badge-warning';
        default: return 'badge-inactive';
    }
};

const getStatusMasukBadgeClass = (s) => {
    switch(s) {
        case 'Baru': return 'badge-active';
        case 'Pindahan': return 'badge-warning';
        default: return 'badge-inactive';
    }
};

const handleFotoChange = (event) => {
    const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
    form.foto = file;
    if (fotoObjectUrl) {
        URL.revokeObjectURL(fotoObjectUrl);
        fotoObjectUrl = null;
    }
    if (file) {
        fotoObjectUrl = URL.createObjectURL(file);
        fotoPreview.value = fotoObjectUrl;
    } else {
        fotoPreview.value = '';
    }
};

const triggerFotoUpload = () => {
    if (fotoInput.value) fotoInput.value.click();
};

const getFotoUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage')) return path;
    return `/storage/${path}`;
};

const formatTtl = (student) => {
    const tempat = student?.tempat_lahir || '-';
    const tanggal = student?.tanggal_lahir ? formatDateShort(student.tanggal_lahir) : '-';
    if (tempat === '-' && tanggal === '-') return '-';
    return `${tempat}, ${tanggal}`;
};

const formatDateShort = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getAge = (dateStr) => {
    if (!dateStr) return '-';
    const birth = new Date(dateStr);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age -= 1;
    }
    return `${age} th`;
};

const availableYears = computed(() => {
    return tahunAjarans.value;
});

const openMutasiModal = (student) => {
    mutasiStudent.value = student;
    mutasiForm.jenis = 'Pindah';
    mutasiForm.tanggal = getTodayString();
    mutasiForm.dari_ke = '';
    showMutasiModal.value = true;
};

const closeMutasiModal = () => {
    showMutasiModal.value = false;
    mutasiStudent.value = null;
    mutasiForm.jenis = '';
    mutasiForm.tanggal = '';
    mutasiForm.dari_ke = '';
};

const submitMutasi = async () => {
    if (!mutasiStudent.value) return;
    
    mutasiForm.loading = true;
    try {
        if (!mutasiForm.jenis) {
            showToast('error', 'Validasi', 'Pilih jenis mutasi terlebih dahulu.');
            return;
        }
        if (!mutasiForm.tanggal) {
            showToast('error', 'Validasi', 'Tanggal keluar wajib diisi.');
            return;
        }
        if (!mutasiForm.dari_ke) {
            showToast('error', 'Validasi', 'Nama sekolah tujuan / alasan wajib diisi.');
            return;
        }
        const statusMap = {
            'Pindah': 'Pindah',
            'Mengundurkan Diri': 'Mengundurkan Diri',
            'Dikeluarkan': 'Dikeluarkan',
        };
        const newStatus = statusMap[mutasiForm.jenis] || 'Keluar';
        const tahunKeluar = mutasiForm.tanggal ? mutasiForm.tanggal.split('-')[0] : '';
        await axios.put(`/api/admin/students/${mutasiStudent.value.id}`, {
            nis: mutasiStudent.value.nis,
            nisn: mutasiStudent.value.nisn,
            nama: mutasiStudent.value.nama,
            jenis_kelamin: mutasiStudent.value.jenis_kelamin,
            tempat_lahir: mutasiStudent.value.tempat_lahir,
            tanggal_lahir: mutasiStudent.value.tanggal_lahir,
            alamat: mutasiStudent.value.alamat,
            nama_ayah: mutasiStudent.value.nama_ayah,
            nama_ibu: mutasiStudent.value.nama_ibu,
            kelas_id: mutasiStudent.value.kelas_id || mutasiStudent.value.kelas?.id,
            status: newStatus,
            tahun_keluar: tahunKeluar,
            tanggal_keluar: mutasiForm.tanggal,
            dari_ke: mutasiForm.dari_ke,
            keterangan: ''
        });
        
        showToast('success', 'Berhasil', `Mutasi ${mutasiStudent.value.nama} berhasil disimpan.`);
        fetchData(currentPage.value);
        closeMutasiModal();
    } catch (e) {
        const msg = e.response?.data?.message || 'Gagal memproses mutasi siswa.';
        showToast('error', 'Gagal', msg);
    } finally {
        mutasiForm.loading = false;
    }
};

const getTodayString = () => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
};



onMounted(() => fetchData());
</script>

<style scoped>
/* Base Styles Resembling Teachers UI */
.page-wrapper {
    font-family: 'Public Sans', sans-serif;
    color: #0f172a;
    padding: 2rem 2.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Header */
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.detailed-header {
  background-color: white;
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  margin-bottom: 24px;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 900;
    letter-spacing: -0.025em;
    color: #0f172a;
    margin-bottom: 0.25rem;
    line-height: 1;
}

.page-subtitle {
    font-size: 0.875rem;
    font-weight: 500;
    color: #64748b;
}

.header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.btn-action {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: #f1f5f9;
  color: #475569;
  padding: 0.75rem 1.25rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 0.875rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:hover {
  background-color: #e2e8f0;
  color: #0f172a;
}

.btn-add-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background-color: #1e40af;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    font-weight: 700;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.2);
    transition: transform 0.2s;
}

.btn-add-primary:hover {
    background-color: #1e3a8a;
    transform: translateY(-2px);
}

.btn-import {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: #10b981;
  color: white;
  padding: 0.75rem 1.25rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 0.875rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

.btn-import:hover {
  background-color: #059669;
  transform: translateY(-1px);
}

.icon-xl { font-size: 1.25rem; }

/* Filter & Search */
.filter-container {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 1.5rem;
    padding: 1rem;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 300px;
    max-width: 400px;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 3rem;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    font-size: 0.875rem;
    outline: none;
    transition: all 0.2s;
}

.search-input:focus {
    border-color: #1e40af;
    box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
}

.filter-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-right: 0.5rem;
}

.custom-select {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #475569;
    padding: 0.75rem 2.5rem 0.75rem 1rem;
    outline: none;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25em 1.25em;
}

/* Table */
.table-container {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 1.5rem;
    box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

.premium-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.premium-table th.th-blue {
    background-color: #4f46e5 !important;
    color: white !important;
    padding: 1.25rem 1.5rem;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.data-row {
    transition: background-color 0.2s;
}

.data-row:hover {
    background-color: rgba(248, 250, 252, 0.8);
}

.premium-table td {
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #f8fafc;
    vertical-align: middle;
}

.student-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.avatar {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
}

.student-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
}
.text-xs { font-size: 0.75rem; }
.text-muted { color: #64748b; }
.text-sm { font-size: 0.875rem; }
.text-slate-600 { color: #475569; }
.text-primary { color: #1e40af; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 900;
    text-transform: uppercase;
    display: inline-block;
}

/* Base Avatar Colors Native */
.bg-blue { background-color: #dbeafe; color: #2563eb; }
.bg-purple { background-color: #f3e8ff; color: #9333ea; }
.bg-amber { background-color: #fef3c7; color: #d97706; }
.bg-emerald { background-color: #d1fae5; color: #059669; }
.bg-rose { background-color: #ffe4e6; color: #e11d48; }

.badge-active { background-color: #d1fae5; color: #047857; }
.badge-inactive { background-color: #f1f5f9; color: #64748b; }
.badge-success { background-color: #dcfce7; color: #15803d; }
.badge-warning { background-color: #fef9c3; color: #a16207; }
.badge-danger { background-color: #fee2e2; color: #b91c1c; }

/* Actions */
.action-group { display: flex; justify-content: flex-end; gap: 0.5rem; }
.btn-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid transparent;
    transition: all 0.2s;
    background: transparent;
    cursor: pointer;
}

.btn-blue { background: #eff6ff; color: #2563eb; border-color: #dbeafe; }
.btn-blue:hover { background: #2563eb; color: white; }

.btn-amber { background: #fffbeb; color: #d97706; border-color: #fef3c7; }
.btn-amber:hover { background: #d97706; color: white; }

.btn-red { background: #fef2f2; color: #ef4444; border-color: #fee2e2; }
.btn-red:hover { background: #ef4444; color: white; }

/* Footer & Pagination */
.table-footer {
    padding: 1.5rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-info {
    font-size: 0.75rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.pagination-group { display: flex; gap: 0.5rem; }
.page-btn {
    padding: 0.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    background: white;
    color: #94a3b8;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.page-btn:hover:not(:disabled) { background: #f8fafc; }
.page-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.page-num {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    border: none;
    font-weight: 700;
    font-size: 0.75rem;
    cursor: pointer;
    background: transparent;
    color: #64748b;
}

.page-num.active {
    background: #1e40af;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.2);
}

/* Empty State */
.empty-state { text-align: center; padding: 4rem; color: #94a3b8; }
.empty-icon { font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.5; }

/* App Footer */
.app-footer { margin-top: 3rem; text-align: center; padding-bottom: 2rem; }
.footer-separator { height: 1px; background: #e2e8f0; margin-bottom: 1.5rem; width: 100%; }
.footer-copy { font-size: 0.65rem; font-weight: 700; color: #94a3b8; letter-spacing: 0.2em; text-transform: uppercase; }

/* Modal Styles */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-panel-premium, .modal-panel {
    background: white;
    width: 100%;
    max-width: 600px;
    border-radius: 1.25rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    max-height: 90vh;
}

.modal-header-premium {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    padding: 1.5rem 2rem; display: flex; align-items: center; justify-content: space-between;
    position: relative; overflow: hidden;
}
.modal-header-premium::after {
    content: ''; position: absolute; right: 0; top: 0; width: 30%; height: 100%;
    background: linear-gradient(to left, rgba(255,255,255,0.1) 0%, transparent 100%);
    pointer-events: none;
}
.header-left { display: flex; align-items: center; gap: 1rem; color: white; position: relative; z-index: 1;}
.header-icon-box {
    background-color: rgba(255,255,255,0.2); padding: 0.75rem; border-radius: 0.75rem;
    backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.3); display: flex;
}
.icon-2xl { font-size: 1.75rem; }
.modal-heading-premium { font-size: 1.25rem; font-weight: 800; margin: 0; line-height: 1.2; letter-spacing: -0.02em;}
.modal-subheading-premium { color: #dbeafe; font-size: 0.85rem; margin: 0.25rem 0 0 0; font-weight: 500;}

.close-btn {
    color: rgba(255,255,255,0.8); background: rgba(0,0,0,0.1); border: none; padding: 0.5rem;
    border-radius: 50%; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;
    position: relative; z-index: 1;
}
.close-btn:hover { color: white; background-color: rgba(255,255,255,0.2); transform: rotate(90deg);}

.modal-body-premium {
    flex: 1; overflow-y: auto; padding: 2rem; background-color: #f8fafc;
}

.form-content { display: flex; flex-direction: column; gap: 2rem; }

.form-section {
    background: white; border-radius: 1rem; padding: 1.5rem;
    border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.highlight-section { border: 1px solid #bfdbfe; background: #eff6ff; }

.section-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; }
.step-badge {
    display: inline-flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem;
    border-radius: 0.5rem; background-color: #1e40af; color: white; font-size: 0.75rem; font-weight: 800;
}
.section-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0; }
.section-divider { flex: 1; height: 1px; background: linear-gradient(to right, #e2e8f0, transparent); }

.form-grid-2 { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
@media (min-width: 768px) { .form-grid-2 { grid-template-columns: 1fr 1fr; } }
.span-full { grid-column: 1 / -1; }

.input-label { display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 0.4rem; }
.text-input, .select-input, .textarea-input {
    width: 100%; padding: 0.625rem 0.875rem; background-color: #f8fafc; border: 1px solid #cbd5e1;
    border-radius: 0.5rem; font-size: 0.875rem; color: #0f172a; transition: all 0.2s; outline: none; box-sizing: border-box;
}
.text-input:focus, .select-input:focus, .textarea-input:focus {
    background-color: #ffffff; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-footer-premium {
    padding: 1.25rem 2rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;
    gap: 0.75rem; background-color: #ffffff;
}
.btn-secondary {
    padding: 0.625rem 1.25rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; color: #475569;
    font-weight: 600; background: #ffffff; cursor: pointer; transition: all 0.2s; font-size: 0.875rem;
}
.btn-secondary:hover { background: #f1f5f9; color: #0f172a; }
.btn-save-premium {
    padding: 0.625rem 1.5rem; border-radius: 0.5rem; background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); color: white;
    font-weight: 700; display: flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3); transition: all 0.2s; font-size: 0.875rem;
}
.btn-save-premium:hover { transform: translateY(-1px); box-shadow: 0 6px 8px -1px rgba(37, 99, 235, 0.4); }


/* Small Modal specifically for Delete Warning */
.modal-xs {
    max-width: 400px;
    padding: 2rem;
    text-align: center;
}
.center-content { align-items: center; justify-content: center; }
.danger-icon-box {
    width: 4rem; height: 4rem;
    background: #fee2e2; color: #ef4444; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;
}
.modal-heading-center { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;}
.modal-text-center { font-size: 0.875rem; color: #64748b; margin-bottom: 2rem;}
.modal-footer-center { display: flex; justify-content: center; gap: 1rem;}
.btn-danger {
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem; font-weight: 600; color: white; background: #ef4444; border: none; cursor: pointer;
}
.btn-danger:hover { background: #dc2626; }

/* Import UI Styles */
.import-container { display: flex; flex-direction: column; gap: 1.5rem; }
.template-section {
    display: flex; gap: 1rem; padding: 1.25rem; background: #f0f9ff;
    border: 1px dashed #7dd3fc; border-radius: 1rem; align-items: flex-start;
}
.info-icon { color: #0284c7; }
.btn-download-template {
    display: inline-flex; align-items: center; gap: 0.5rem; background: white;
    padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;
    font-size: 0.8rem; font-weight: 700; color: #0369a1; cursor: pointer; transition: all 0.2s;
}
.btn-download-template:hover { background: #f8fafc; border-color: #bae6fd; color: #0284c7; }

.upload-zone {
    border: 2px dashed #e2e8f0; border-radius: 1.25rem; padding: 3rem 2rem;
    text-align: center; cursor: pointer; transition: all 0.2s; background: white;
}
.upload-zone:hover { border-color: #3b82f6; background: #f8fafc; }
.uploading { opacity: 0.6; pointer-events: none; }
.upload-label { cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.upload-icon { font-size: 3.5rem; color: #94a3b8; }
.upload-text { font-size: 0.95rem; color: #475569; }
.icon-xs { font-size: 1.1rem; }

/* ===== Mutasi Modal ===== */
.mutasi-modal {
    background: #ffffff;
    width: 100%;
    max-width: 900px;
    border-radius: 2.5rem;
    overflow: hidden;
    box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}
.mutasi-header {
    padding: 1.1rem 1.6rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}
.mutasi-header-left { display: flex; align-items: center; gap: 1rem; }
.mutasi-header-icon {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 1rem;
    background: #4f46e5;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
}
.mutasi-title { font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0; }
.mutasi-subtitle { font-size: 0.72rem; color: #94a3b8; margin-top: 0.2rem; font-weight: 500; }
.mutasi-close {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 999px;
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
}
.mutasi-close:hover { background: #f1f5f9; }

.mutasi-info-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    gap: 1.5rem;
}
.mutasi-info { display: flex; align-items: center; gap: 0.75rem; }
.mutasi-info-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: #e0e7ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
}
.mutasi-info-label {
    font-size: 0.6rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.25rem;
}
.mutasi-info-name { font-size: 0.9rem; font-weight: 800; color: #0f172a; }
.mutasi-info-nis { color: #64748b; font-weight: 700; }
.mutasi-sep { margin: 0 0.5rem; color: #cbd5e1; font-weight: 600; }
.mutasi-info-right { text-align: right; }
.mutasi-info-class { font-size: 0.9rem; font-weight: 800; color: #475569; }

.mutasi-body { padding: 2rem; overflow-y: auto; }
.mutasi-form { display: flex; flex-direction: column; gap: 2rem; }
.mutasi-type-header { text-align: center; }
.mutasi-type-title { font-size: 0.8rem; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.12em; }
.mutasi-type-subtitle { font-size: 0.8rem; color: #94a3b8; margin-top: 0.4rem; }
.mutasi-cards {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.5rem;
}
.mutasi-card {
    position: relative;
    border: 2px solid #f1f5f9;
    border-radius: 2rem;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.6rem;
    text-align: center;
    cursor: pointer;
    background: #ffffff;
    transition: all 0.2s;
}
.mutasi-card:hover { border-color: #c7d2fe; background: #f8fafc; }
.mutasi-card.active {
    border-color: #4f46e5;
    background: #eef2ff;
    box-shadow: 0 8px 18px -12px rgba(79, 70, 229, 0.4);
}
.mutasi-card-icon {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 1rem;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.mutasi-card.active .mutasi-card-icon {
    background: #4f46e5;
    color: #ffffff;
    box-shadow: 0 10px 16px -12px rgba(79, 70, 229, 0.6);
}
.mutasi-card-title { font-size: 1rem; font-weight: 800; color: #0f172a; }
.mutasi-card-subtitle { font-size: 0.75rem; color: #94a3b8; }
.mutasi-check {
    position: absolute;
    top: -0.6rem;
    right: -0.6rem;
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    background: #4f46e5;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.6);
    transition: all 0.2s;
}
.mutasi-card.active .mutasi-check { opacity: 1; transform: scale(1); }

.mutasi-details {
    border-top: 1px solid #f1f5f9;
    padding-top: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.mutasi-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; }

.mutasi-footer {
    padding: 1.25rem 2rem;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}
.mutasi-footnote { font-size: 0.7rem; color: #94a3b8; }
.mutasi-actions { display: flex; gap: 0.75rem; align-items: center; }

@media (max-width: 768px) {
    .mutasi-info-bar { flex-direction: column; align-items: flex-start; }
    .mutasi-info-right { text-align: left; }
    .mutasi-cards { grid-template-columns: 1fr; }
    .mutasi-grid { grid-template-columns: 1fr; }
    .mutasi-footer { flex-direction: column; align-items: flex-start; }
}

/* New Students UI (aligned with Guru/Mapel) */
.students-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
@media (min-width: 768px) {
    .students-page { padding: 2rem; }
}

.page-header {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.header-blue {
    background: var(--color-primary, #1e40af);
    color: #ffffff;
    padding: 1.5rem 1.75rem;
    border-radius: 1.5rem;
    box-shadow: 0 12px 24px -18px rgba(30, 64, 175, 0.6);
}
.header-content { flex: 1; min-width: 0; }
.page-title {
    font-size: 1.55rem;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: -0.025em;
    margin-bottom: 0.35rem;
    line-height: 1.2;
}
.page-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.8rem;
    font-weight: 500;
}
.header-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.header-btn {
    border-radius: 999px;
    padding: 0.6rem 1.1rem;
    font-size: 0.85rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.btn-primary {
    background-color: #ffffff;
    color: var(--color-primary, #1e40af);
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 6px 14px -8px rgba(15, 23, 42, 0.4);
}
.btn-primary:hover { background-color: #e2e8f0; }
.btn-secondary {
    background-color: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.3);
}
.btn-secondary:hover { background-color: rgba(255, 255, 255, 0.3); }
@media (min-width: 768px) {
    .page-header { flex-direction: row; align-items: center; justify-content: space-between; }
}

.table-filters {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 0.75rem 1rem;
}
.filter-item {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 0.45rem 0.75rem;
    border-radius: 999px;
    color: #475569;
}
.filter-item.search { flex: 1; min-width: 240px; }
.filter-item input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 0.8rem;
    font-weight: 600;
    color: #0f172a;
}
.filter-item select {
    background: transparent;
    border: none;
    outline: none;
    font-size: 0.78rem;
    font-weight: 700;
    color: #0f172a;
    appearance: none;
    cursor: pointer;
}
.filter-meta {
    margin-left: auto;
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 700;
}
@media (max-width: 768px) {
    .filter-meta { width: 100%; margin-left: 0; }
}

.card {
    background: #ffffff;
    border-radius: 1.5rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    overflow: hidden;
}
.table-responsive { overflow-x: auto; }
.data-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}
.data-table th {
    padding: 1rem 1.5rem;
    font-size: 0.625rem;
    font-weight: 700;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background-color: #1e40af;
    border-bottom: 2px solid #1e3a8a;
}
.data-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.hover-row:hover td { background-color: rgba(248, 250, 252, 0.8); }
.text-right { text-align: right; }
.pr-6 { padding-right: 1.5rem; }

.pagination-footer {
    padding: 1.5rem;
    background-color: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.showing-text {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
}
.pagination-controls {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.page-btn {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    border: none;
    background: none;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}
.page-btn:hover { background-color: #f1f5f9; }
.page-btn.active { background-color: var(--color-primary, #1e40af); color: #ffffff; }
.page-btn.nav { border: 1px solid #e2e8f0; }

/* Student Modal */
.student-modal {
    background: #ffffff;
    width: 100%;
    max-width: 900px;
    border-radius: 2.5rem;
    box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;
}
.student-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-header-left { display: flex; align-items: center; gap: 1rem; }
.modal-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: #4f46e5;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
}
.modal-title { font-size: 1rem; font-weight: 800; color: #0f172a; }
.modal-subtitle { font-size: 0.7rem; color: #94a3b8; font-weight: 500; }
.modal-close {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 999px;
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
}
.modal-close:hover { background: #f1f5f9; }

.step-progress {
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
}
.step-progress > div { flex: 1; }
.step-block { display: flex; flex-direction: column; gap: 0.5rem; }
.step-bar { height: 6px; border-radius: 999px; background: #e2e8f0; }
.step-bar.active { background: #4f46e5; }
.step-block p { font-size: 0.7rem; font-weight: 800; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.08em; }
.step-block.active p { color: #4f46e5; }

.student-modal-body {
    padding: 2rem;
    overflow-y: auto;
}
.student-form { display: flex; flex-direction: column; gap: 2rem; }
.form-section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 1.5rem;
}
.section-chip {
    width: 2rem;
    height: 2rem;
    border-radius: 0.75rem;
    background: #eef2ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
}
.section-chip.green { background: #ecfdf3; color: #10b981; }
.section-chip.orange { background: #fff7ed; color: #f97316; }
.grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
.grid-3 { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
.grid-3-col { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.5rem; }
.grid-4-col { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.5rem; }
@media (max-width: 768px) {
  .grid-2, .grid-3, .grid-3-col, .grid-4-col { grid-template-columns: 1fr; }
}
.span-2 { grid-column: span 2; }
.field { display: flex; flex-direction: column; gap: 0.4rem; }
.field label { font-size: 0.75rem; font-weight: 700; color: #334155; }
.field-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #334155;
}
.label-note {
    margin-left: auto;
    font-size: 0.65rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.required-star { color: #ef4444; font-weight: 800; }
.label-icon {
    font-size: 1.1rem;
    color: #94a3b8;
}
.label-icon.highlight { color: #6366f1; }
.label-icon.purple { color: #8b5cf6; }
.label-icon.orange { color: #f97316; }
.label-icon.blue { color: #3b82f6; }
.label-icon.red { color: #ef4444; }
.label-icon.green { color: #10b981; }
.label-icon.amber { color: #f59e0b; }
.label-icon.pink { color: #ec4899; }
.input {
    width: 100%;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 0.7rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #0f172a;
}
.file-input {
    padding: 0.75rem 1rem;
    background: #f8fafc;
}
.photo-field { gap: 0.8rem; }
.photo-uploader {
    display: flex;
    gap: 1.25rem;
    align-items: center;
    padding: 0.9rem 1rem;
    border-radius: 1.25rem;
    border: 1px dashed #e2e8f0;
    background: #f8fafc;
}
.photo-preview {
    width: 90px;
    height: 90px;
    border-radius: 1.25rem;
    background: #eef2ff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    color: #6366f1;
    flex-shrink: 0;
    border: 1px solid #e0e7ff;
}
.photo-preview.empty {
    background: #f1f5f9;
    color: #94a3b8;
    border-color: #e2e8f0;
}
.photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.photo-actions {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.btn-photo {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border-radius: 999px;
    border: 1px solid #e0e7ff;
    background: #ffffff;
    color: #4f46e5;
    font-weight: 800;
    font-size: 0.75rem;
    cursor: pointer;
}
.btn-photo:hover { background: #eef2ff; }
.file-name-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    background: #ecfdf5;
    color: #047857;
    font-size: 0.65rem;
    font-weight: 800;
    max-width: 220px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
    background: #ffffff;
}
.input.textarea { resize: none; }
.input.error { border-color: #ef4444; background: #fef2f2; }
.error-text { font-size: 0.7rem; color: #ef4444; font-weight: 700; min-height: 0.9rem; }
.helper-text { font-size: 0.7rem; color: #94a3b8; font-weight: 600; min-height: 0.9rem; }
.form-note { font-size: 0.75rem; color: #94a3b8; font-style: italic; }

.student-modal-footer {
    padding: 1.5rem 2rem;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}
.footer-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
}
.btn-secondary-action {
    padding: 0.8rem 1.5rem;
    border-radius: 1.25rem;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-secondary-action:hover { background: #f1f5f9; color: #0f172a; }
.btn-secondary-action:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.btn-cancel {
    padding: 0.8rem 1.75rem;
    border-radius: 1.25rem;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-cancel:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.btn-primary-action {
    padding: 0.85rem 2rem;
    border-radius: 1.25rem;
    border: none;
    background: #4f46e5;
    color: #ffffff;
    font-weight: 800;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
    cursor: pointer;
    transition: all 0.2s;
}
.btn-primary-action:hover { background: #4338ca; }

@media (max-width: 768px) {
    .grid-2, .grid-3 { grid-template-columns: 1fr; }
    .span-2 { grid-column: span 1; }
    .student-modal-header, .student-modal-body, .student-modal-footer { padding: 1.5rem; }
    .step-progress { padding: 0 1.5rem; }
}

/* Import Modal (match Teachers) */
.import-modal {
    background: #ffffff;
    width: 100%;
    max-width: 640px;
    border-radius: 2.5rem;
    box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;
}
.import-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}
.import-header .header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: #0f172a;
}
.header-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 1rem;
    background: #4f46e5;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
}
.import-body {
    padding: 1.5rem 2rem;
    background: #f8fafc;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.import-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1.5rem;
    padding: 1.25rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.import-card-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
}
.step-chip {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 0.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.75rem;
}
.step-indigo { background: #eef2ff; color: #4f46e5; }
.import-card p { font-size: 0.8rem; color: #64748b; line-height: 1.5; }
.btn-download {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border-radius: 1rem;
    background: #eef2ff;
    color: #4f46e5;
    font-weight: 800;
    font-size: 0.75rem;
    border: 1px solid #e0e7ff;
    cursor: pointer;
    width: fit-content;
}
.dropzone {
    border: 2px dashed #e2e8f0;
    border-radius: 1.5rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    background: #f8fafc;
}
.hidden-input { display: none; }
.dropzone span { font-size: 2rem; color: #94a3b8; }
.dropzone-title { font-size: 0.8rem; font-weight: 800; color: #0f172a; }
.dropzone-subtitle { font-size: 0.7rem; color: #94a3b8; }
.dropzone-active { border-color: #6366f1; background: #eef2ff; }
.file-pill {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 0.9rem;
    background: #ecfdf5;
    border: 1px solid #d1fae5;
    border-radius: 1rem;
}
.file-pill span { color: #10b981; }
.file-meta { flex: 1; min-width: 0; }
.file-name { font-size: 0.8rem; font-weight: 800; color: #065f46; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-ready { font-size: 0.6rem; color: #10b981; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; }
.file-remove { background: transparent; border: none; color: #94a3b8; cursor: pointer; }
.import-error {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 0.9rem;
    border-radius: 0.9rem;
    background: #fef2f2;
    border: 1px solid #fee2e2;
    color: #ef4444;
    font-weight: 700;
    font-size: 0.75rem;
}
.import-footer {
    padding: 1.25rem 2rem;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
.icon-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
/* Family Section */
.family-space { display: flex; flex-direction: column; gap: 2rem; }
.family-block { padding-left: 1rem; border-left: 4px solid; padding-top: 0.5rem; padding-bottom: 0.5rem; }
.father-block { border-color: #e0e7ff; }
.mother-block { border-color: #fce7f3; }
.guardian-block { border-color: #d1fae5; }
.family-title {
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.family-title::before {
    content: '';
    display: inline-block;
    width: 0.6rem;
    height: 0.6rem;
    border-radius: 999px;
}
.father-title { color: #4f46e5; }
.father-title::before { background-color: #4f46e5; }
.mother-title { color: #db2777; }
.mother-title::before { background-color: #db2777; }
.guardian-title { color: #059669; }
.guardian-title::before { background-color: #059669; }

/* Custom Modal Scroll */
.main-scrollable {
    flex: 1;
    overflow-y: auto;
    max-height: 80vh;
    padding: 1rem 1.5rem;
    background-color: #f8fafc;
}

/* User tailwind custom CSS */
.form-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1rem 1.25rem;
    transition: all 0.2s;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    margin-bottom: 1rem;
}
.form-card:hover {
    border-color: #c7d2fe;
}
.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.icon-box {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-indigo-50 { background-color: #eef2ff; color: #4f46e5; }
.bg-emerald-50 { background-color: #ecfdf3; color: #10b981; }
.bg-orange-50 { background-color: #fff7ed; color: #ea580c; }
.bg-blue-50 { background-color: #eff6ff; color: #2563eb; }
.bg-purple-50 { background-color: #faf5ff; color: #9333ea; }
.bg-rose-50 { background-color: #fff1f2; color: #e11d48; }

.section-title {
    font-size: 0.9rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 0.1rem;
}
.section-subtitle {
    font-size: 0.6rem;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: -0.02rem;
}

.single-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    background: #ffffff;
}

.submit-btn {
    padding: 0.6rem 1.5rem;
    border-radius: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background-color: #4f46e5;
    color: white;
    font-size: 0.85rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4);
}
.submit-btn:hover { background-color: #4338ca; }
</style>




