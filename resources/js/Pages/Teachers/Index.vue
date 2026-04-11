<template>
  <div class="teachers-page">
    <!-- Header -->
    <div class="page-header header-blue">
      <div class="header-content">
        <h1 class="page-title">Data Guru</h1>
        <p class="page-subtitle">Manajemen data tenaga pendidik di lingkungan lembaga.</p>
      </div>
      <div class="header-actions">
          <button class="btn-secondary header-btn" @click="openImportModal">
            <span class="material-symbols-outlined icon">upload</span>
            Import
          </button>
          <button class="btn-primary header-btn" @click="openModal">
            <span class="material-symbols-outlined icon">person_add</span>
            Tambah Guru
          </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="table-filters">
      <div class="filter-item search">
        <span class="material-symbols-outlined">search</span>
        <input v-model="searchQuery" type="text" placeholder="Cari NIP, Nama atau Email guru..." />
      </div>
      <div class="filter-item">
        <span class="material-symbols-outlined">tune</span>
        <select v-model="filterStatus">
          <option value="Semua">Semua Status</option>
          <option value="PNS">Tetap (PNS)</option>
          <option value="Honorer">Kontrak / Honorer</option>
          <option value="PPPK">PPPK</option>
        </select>
      </div>
      <div class="filter-meta">
        Menampilkan <strong>{{ filteredGurus.length }}</strong> data
      </div>
    </div>

    <!-- Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Nama Guru</th>
              <th>NIP / NIK</th>
              <th>Kelamin</th>
              <th>Lahir</th>
              <th>Status</th>
              <th>Kontak</th>
              <th>Akun & Password</th>
              <th class="text-right pr-6">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(guru, index) in filteredGurus" :key="guru.id" class="hover-row">
              <td class="td-name-width">
                <div class="profile-group">
                  <div class="profile-text">
                    <p class="text-name">{{ guru.nama }}</p>
                  </div>
                </div>
              </td>
              <td>
                <p class="text-nip">{{ guru.nip || '-' }}</p>
                <p class="text-nik">NIK: {{ guru.nik || '-' }}</p>
              </td>
              <td class="td-gender-width">
                <span class="text-gender">{{ guru.jenis_kelamin }}</span>
              </td>
              <td>
                <p class="text-dob-city">{{ guru.tempat_lahir || '-' }},</p>
                <p class="text-dob-date">{{ formatDate(guru.tanggal_lahir) }}</p>
              </td>
              <td>
                <span class="status-badge" :class="getStatusBadgeClass(guru.status_kepegawaian)">
                  {{ guru.status_kepegawaian || '-' }}
                </span>
              </td>
              <td>
                <div class="contact-list">
                  <div class="contact-item">
                    <span class="material-symbols-outlined icon-xs">phone</span>
                    {{ guru.no_hp || '-' }}
                  </div>
                  <div class="contact-item">
                    <span class="material-symbols-outlined icon-xs">mail</span>
                    {{ guru.email || '-' }}
                  </div>
                </div>
              </td>
              <td>
                <div class="account-info">
                   <div class="password-display">
                        <span class="password-text">
                            {{ visiblePasswords[guru.id] ? (guru.plain_password || '-') : '********' }}
                        </span>
                        <button class="btn-icon-xs" @click="togglePassword(guru.id)" title="Lihat Password">
                            <span class="material-symbols-outlined icon-xs">
                                {{ visiblePasswords[guru.id] ? 'visibility_off' : 'visibility' }}
                            </span>
                        </button>
                   </div>
                </div>
              </td>
              <td class="text-right pr-6">
                <div class="action-group">
                  <button class="btn-icon btn-blue" title="Detail" @click="openDetail(guru)">
                    <span class="material-symbols-outlined icon-sm">visibility</span>
                  </button>
                  <button class="btn-icon btn-amber" title="Edit" @click="editGuru(guru)">
                    <span class="material-symbols-outlined icon-sm">edit</span>
                  </button>
                  <button class="btn-icon btn-red" title="Hapus" @click="deleteGuru(guru.id)">
                    <span class="material-symbols-outlined icon-sm">delete</span>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredGurus.length === 0">
                <td colspan="8" class="empty-state">
                    <span class="material-symbols-outlined empty-icon">group_off</span>
                    <p>Belum ada data guru ditemukan.</p>
                </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination-footer">
        <p class="showing-text">Menampilkan {{ filteredGurus.length }} Data Guru</p>
      </div>
    </div>
    
    <footer class="app-footer">
        <div class="footer-separator"></div>
        <p class="footer-copy">(c) 2026 SCHOOL INFORMATION SYSTEM. v2.4.0</p>
    </footer>

    <!-- Modal Tambah/Edit Guru -->
    <div v-if="showModal" class="modal-backdrop">
      <div class="teacher-modal">
        <header class="teacher-modal-header">
          <div class="header-left">
            <div class="header-icon">
              <span class="material-symbols-outlined">person_add</span>
            </div>
            <div>
              <h3 class="modal-title">{{ isEdit ? 'Edit Data Guru' : 'Tambah Data Guru' }}</h3>
              <p class="modal-subtitle">Lengkapi formulir untuk mendaftarkan tenaga pendidik baru</p>
            </div>
          </div>
          <button class="modal-close" @click="closeModal" aria-label="Tutup">
            <span class="material-symbols-outlined">close</span>
          </button>
        </header>

        <main class="teacher-modal-body">
          <form @submit.prevent="submitForm" class="teacher-form">
            <section class="teacher-section">
              <div class="teacher-section-title">
                <span class="step-chip step-indigo">1</span>
                Informasi Identitas
              </div>
              <div class="form-grid">
                <div>
                  <label class="label-compact">NIP (Optional)</label>
                  <input v-model="form.nip" class="input-compact" placeholder="Masukkan NIP" type="text" />
                </div>
                <div>
                  <label class="label-compact">NIK (Optional)</label>
                  <input v-model="form.nik" class="input-compact" maxlength="16" placeholder="16 Digit NIK" type="text" />
                </div>
                <div class="span-2">
                  <label class="label-compact">Nama Lengkap & Gelar (Required)</label>
                  <input v-model="form.nama" class="input-compact" :class="{ 'error-state': errors.nama }" placeholder="Nama Lengkap disertai gelar" required type="text" />
                  <span class="error-text">{{ errors.nama ? errors.nama[0] : '' }}</span>
                </div>
                <div>
                  <label class="label-compact">Jenis Kelamin (Required)</label>
                  <select v-model="form.jenis_kelamin" class="input-compact" :class="{ 'error-state': errors.jenis_kelamin }" required>
                    <option value="">Pilih</option>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                  </select>
                  <span class="error-text">{{ errors.jenis_kelamin ? errors.jenis_kelamin[0] : '' }}</span>
                </div>
                <div>
                  <label class="label-compact">Pendidikan Terakhir (Optional)</label>
                  <select v-model="form.pendidikan_terakhir" class="input-compact">
                    <option value="">Pilih Pendidikan</option>
                    <option value="D3">D3</option>
                    <option value="S1">S1</option>
                    <option value="S2">S2</option>
                    <option value="S3">S3</option>
                  </select>
                </div>
              </div>
            </section>

            <section class="teacher-section">
              <div class="teacher-section-title">
                <span class="step-chip step-orange">2</span>
                Kelahiran & Kontak
              </div>
              <div class="form-grid">
                <div>
                  <label class="label-compact">Tempat Lahir</label>
                  <input v-model="form.tempat_lahir" class="input-compact" placeholder="Kota Kelahiran" type="text" />
                </div>
                <div>
                  <label class="label-compact">Tanggal Lahir</label>
                  <input v-model="form.tanggal_lahir" class="input-compact" type="date" />
                </div>
                <div>
                  <label class="label-compact">Nomor HP/WhatsApp</label>
                  <input v-model="form.no_hp" class="input-compact" placeholder="08xxxx" type="text" />
                </div>
                <div>
                  <label class="label-compact">Email Aktif</label>
                  <input v-model="form.email" class="input-compact" :class="{ 'error-state': errors.email }" placeholder="email@sekolah.sch.id" type="email" />
                  <span class="error-text">{{ errors.email ? errors.email[0] : '' }}</span>
                </div>
                <div class="span-2">
                  <label class="label-compact">Alamat Domisili</label>
                  <textarea v-model="form.alamat" class="input-compact textarea-compact" placeholder="Alamat lengkap saat ini"></textarea>
                </div>
              </div>
            </section>

            <section class="teacher-section">
              <div class="teacher-section-title">
                <span class="step-chip step-emerald">3</span>
                Status Kepegawaian & Akun
              </div>
              <div class="form-grid">
                <div>
                  <label class="label-compact">Status Pegawai</label>
                  <select v-model="form.status_kepegawaian" class="input-compact">
                    <option value="">Pilih Status</option>
                    <option value="PNS">PNS</option>
                    <option value="PPPK">PPPK</option>
                    <option value="Honorer">Honorer</option>
                    <option value="GTY">GTY</option>
                  </select>
                </div>
                <div>
                  <label class="label-compact">Tanggal Masuk</label>
                  <input v-model="form.tanggal_masuk" class="input-compact" type="date" />
                </div>
                <div class="span-2">
                  <label class="label-compact">Password Akses Sistem (Optional)</label>
                  <input v-model="form.password" class="input-compact" :class="{ 'error-state': errors.password }" placeholder="********" type="password" />
                  <p class="helper-text">Kosongkan untuk auto-generate</p>
                  <span class="error-text">{{ errors.password ? errors.password[0] : '' }}</span>
                </div>
              </div>
            </section>
          </form>
        </main>

        <footer class="teacher-modal-footer">
          <button type="button" class="btn-cancel" @click="closeModal">Batal</button>
          <button type="button" class="btn-primary-action" @click="submitForm">
            <span class="material-symbols-outlined">person_add</span>
            {{ isEdit ? 'Simpan Perubahan' : 'Simpan & Tambah Guru' }}
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
            <h3 class="modal-heading-center">Hapus Data Guru?</h3>
            <p class="modal-text-center">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-footer-center">
             <button class="btn-secondary" @click="closeDeleteModal">Batal</button>
             <button class="btn-danger" @click="confirmDelete">Hapus</button>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetailModal" class="modal-backdrop">
      <div class="detail-modal">
        <header class="detail-header">
          <div class="header-left">
            <div class="header-icon">
              <span class="material-symbols-outlined">badge</span>
            </div>
            <div>
              <h3 class="modal-title">Detail Guru</h3>
              <p class="modal-subtitle">Informasi lengkap tenaga pendidik</p>
            </div>
          </div>
          <button class="modal-close" @click="closeDetail" aria-label="Tutup">
            <span class="material-symbols-outlined">close</span>
          </button>
        </header>
        <main class="detail-body">
          <div class="detail-hero">
            <div class="detail-avatar">{{ getInitials(selectedGuru?.nama || '') }}</div>
            <div>
              <p class="detail-name">{{ selectedGuru?.nama || '-' }}</p>
              <p class="detail-meta">{{ selectedGuru?.status_kepegawaian || '-' }} • {{ selectedGuru?.email || '-' }}</p>
            </div>
          </div>
          <div class="detail-grid">
            <div class="detail-card">
              <h4>Identitas</h4>
              <div class="detail-row">
                <span>NIP</span>
                <strong>{{ selectedGuru?.nip || '-' }}</strong>
              </div>
              <div class="detail-row">
                <span>NIK</span>
                <strong>{{ selectedGuru?.nik || '-' }}</strong>
              </div>
              <div class="detail-row">
                <span>Jenis Kelamin</span>
                <strong>{{ selectedGuru?.jenis_kelamin || '-' }}</strong>
              </div>
              <div class="detail-row">
                <span>Pendidikan</span>
                <strong>{{ selectedGuru?.pendidikan_terakhir || '-' }}</strong>
              </div>
            </div>
            <div class="detail-card">
              <h4>Kelahiran & Kontak</h4>
              <div class="detail-row">
                <span>Tempat, Tgl Lahir</span>
                <strong>{{ selectedGuru?.tempat_lahir || '-' }}, {{ formatDate(selectedGuru?.tanggal_lahir) }}</strong>
              </div>
              <div class="detail-row">
                <span>No. HP</span>
                <strong>{{ selectedGuru?.no_hp || '-' }}</strong>
              </div>
              <div class="detail-row">
                <span>Email</span>
                <strong>{{ selectedGuru?.email || '-' }}</strong>
              </div>
              <div class="detail-row">
                <span>Alamat</span>
                <strong>{{ selectedGuru?.alamat || '-' }}</strong>
              </div>
            </div>
            <div class="detail-card">
              <h4>Kepegawaian & Akun</h4>
              <div class="detail-row">
                <span>Status</span>
                <strong>{{ selectedGuru?.status_kepegawaian || '-' }}</strong>
              </div>
              <div class="detail-row">
                <span>Tanggal Masuk</span>
                <strong>{{ formatDate(selectedGuru?.tanggal_masuk) }}</strong>
              </div>
              <div class="detail-row">
                <span>Password</span>
                <strong>{{ selectedGuru?.plain_password || '-' }}</strong>
              </div>
            </div>
          </div>
        </main>
        <footer class="detail-footer">
          <button class="btn-cancel" @click="closeDetail">Tutup</button>
          <button class="btn-primary-action" @click="editGuru(selectedGuru)">Edit</button>
        </footer>
      </div>
    </div>

    <Toast :show="toast.show" :type="toast.type" :title="toast.title" :message="toast.message" @close="toast.show = false" />

    <!-- Import Modal -->
    <div v-if="showImportModal" class="modal-backdrop" @click.self="closeImportModal">
      <div class="import-modal">
        <header class="import-header">
          <div class="header-left">
            <div class="header-icon">
              <span class="material-symbols-outlined">person_add</span>
            </div>
            <div>
              <h2 class="modal-title">Import Data Guru</h2>
              <p class="modal-subtitle">Unggah data guru secara massal menggunakan template</p>
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
            <p>Pastikan data guru Anda sudah sesuai dengan format yang kami tentukan. Silakan unduh template Excel di bawah ini untuk memulai.</p>
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
            <p>Upload file Excel yang sudah diisi data guru.</p>
            <div class="dropzone" :class="{ 'dropzone-active': importFile }" @click="$refs.fileInput.click()">
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
          <button v-if="!isImporting" class="btn-primary-action" @click="submitImport" :disabled="!importFile">
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
  </div>
</template>

<script setup>
import Toast from '@/Components/Toast.vue';
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';

const gurus = ref([]);
const searchQuery = ref('');
const filterStatus = ref('Semua');
const showModal = ref(false);
const showDeleteModal = ref(false);
const showDetailModal = ref(false);
const isEdit = ref(false);
const editId = ref(null);
const deleteId = ref(null);
const selectedGuru = ref(null);
const visiblePasswords = ref({});

const togglePassword = (id) => {
    visiblePasswords.value[id] = !visiblePasswords.value[id];
};

const form = reactive({
    nip: '',
    nik: '',
    nama: '',
    jenis_kelamin: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    no_hp: '',
    email: '',
    alamat: '',
    pendidikan_terakhir: '',
    status_kepegawaian: 'Honorer',
    tanggal_masuk: '',
    password: ''
});

const toast = reactive({ show: false, type: 'success', title: '', message: '' });

const showToast = (type, title, message) => {
    toast.type = type;
    toast.title = title;
    toast.message = message;
    toast.show = true;
};

const fetchData = async () => {
    try {
        const response = await axios.get('/api/admin/teachers');
        gurus.value = response.data;
    } catch (error) {
        showToast('error', 'Error', 'Gagal memuat data guru.');
    }
};


// Import Logic
const showImportModal = ref(false);
const importFile = ref(null);
const isImporting = ref(false);
const importError = ref(null);
const fileInput = ref(null);

const downloadTemplate = () => {
    window.location.href = '/api/admin/teachers/template';
};

const openImportModal = () => {
    showImportModal.value = true;
    importFile.value = null;
    importError.value = null;
    if (fileInput.value) fileInput.value.value = null;
};

const closeImportModal = () => {
    showImportModal.value = false;
    importFile.value = null;
    importError.value = null;
};

const handleFileChange = (event) => {
    importFile.value = event.target.files && event.target.files[0] ? event.target.files[0] : null;
    importError.value = null;
};

const clearImportFile = () => {
    importFile.value = null;
    if (fileInput.value) fileInput.value.value = null;
};

const submitImport = async () => {
    if (!importFile.value) {
        importError.value = "Silahkan pilih file terlebih dahulu.";
        return;
    }

    isImporting.value = true;
    importError.value = null;

    let formData = new FormData();
    formData.append('file', importFile.value);

    try {
        const response = await axios.post('/api/admin/teachers/import', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        showToast('success', 'Berhasil', `Berhasil mengimport ${response.data.count} data guru.`);
        closeImportModal();
        fetchData();
    } catch (e) {
        console.error(e);
        importError.value = e.response?.data?.message || "Terjadi kesalahan saat import.";
        showToast('error', 'Gagal', 'Gagal mengimport data.');
    } finally {
        isImporting.value = false;
    }
};

const filteredGurus = computed(() => {
    return gurus.value.filter((g) => {
        const q = searchQuery.value.toLowerCase();
        const matchesSearch = !searchQuery.value ||
            (g.nama && g.nama.toLowerCase().includes(q)) ||
            (g.nip && g.nip.includes(searchQuery.value)) ||
            (g.email && g.email.toLowerCase().includes(q));
        const matchesStatus = filterStatus.value === 'Semua' || g.status_kepegawaian === filterStatus.value;
        return matchesSearch && matchesStatus;
    });
});

const openModal = () => {
    isEdit.value = false;
    Object.keys(form).forEach(k => form[k] = '');
    form.status_kepegawaian = 'Honorer'; 
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; };
const closeDeleteModal = () => { showDeleteModal.value = false; deleteId.value = null; };

const editGuru = (item) => {
    isEdit.value = true;
    editId.value = item.id;
    Object.keys(form).forEach(k => form[k] = item[k] || '');
    form.password = '';
    showModal.value = true;
};

const openDetail = (item) => {
    selectedGuru.value = item;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
    selectedGuru.value = null;
};

const errors = ref({});

const submitForm = async () => {
    errors.value = {}; // Reset errors
    try {
        if (isEdit.value) {
            await axios.put(`/api/admin/teachers/${editId.value}`, form);
            showToast('success', 'Berhasil', 'Data diperbarui');
        } else {
            await axios.post('/api/admin/teachers', form);
            showToast('success', 'Berhasil', 'Guru ditambahkan');
        }
        closeModal();
        fetchData();
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

const deleteGuru = (id) => { deleteId.value = id; showDeleteModal.value = true; };

const confirmDelete = async () => {
    try {
        await axios.delete(`/api/admin/teachers/${deleteId.value}`);
        showToast('success', 'Terhapus', 'Data guru dihapus');
        fetchData();
        closeDeleteModal();
    } catch (e) {
        showToast('error', 'Gagal', 'Gagal menghapus');
    }
};

const getInitials = (name) => name ? name.substring(0, 2).toUpperCase() : '??';
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';

const getAvatarColorClass = (index) => {
    const classes = ['bg-blue', 'bg-purple', 'bg-amber', 'bg-emerald', 'bg-rose'];
    return classes[index % classes.length];
};

const getStatusBadgeClass = (s) => {
    switch(s) {
        case 'PNS': return 'badge-pns';
        case 'PPPK': return 'badge-pppk';
        case 'Honorer': return 'badge-honorer';
        default: return 'badge-default';
    }
};

onMounted(() => fetchData());
</script>

<style scoped>
/* Page & Layout */
.teachers-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    font-family: 'Public Sans', sans-serif;
    color: #0f172a;
}
@media (min-width: 768px) {
    .teachers-page { padding: 2rem; }
}

/* Header */
.page-header {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.header-content { flex: 1; min-width: 0; }
.header-blue {
    background: var(--color-primary, #1e40af);
    color: #ffffff;
    padding: 1.5rem 1.75rem;
    border-radius: 1.5rem;
    box-shadow: 0 12px 24px -18px rgba(30, 64, 175, 0.6);
}
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
    flex-wrap: nowrap;
    margin-left: auto;
}
.header-btn {
    border-radius: 999px;
    padding: 0.6rem 1.1rem;
    font-size: 0.85rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
}
.btn-primary {
    background-color: #ffffff;
    color: var(--color-primary, #1e40af);
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 6px 14px -8px rgba(15, 23, 42, 0.4);
}
.btn-primary:hover {
    background-color: #e2e8f0;
    border-color: rgba(226, 232, 240, 0.9);
}
.btn-secondary {
    background-color: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.3);
}
.btn-secondary:hover {
    background-color: rgba(255, 255, 255, 0.3);
}

.btn-add-primary:hover {
    background-color: #1e3a8a;
    transform: translateY(-2px);
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
    overflow-x: auto;
}

.filter-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-right: 0.5rem;
    white-space: nowrap;
}

.filter-chip {
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    border: 1px solid #e2e8f0;
    background-color: #f8fafc;
    color: #64748b;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}

.filter-chip.active {
    background-color: #1e40af;
    color: white;
    border-color: #1e40af;
}

.filter-chip:hover:not(.active) {
    background-color: #f1f5f9;
}

/* Filters */
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
.filter-item.search {
    flex: 1;
    min-width: 220px;
}
.filter-item input {
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.8rem;
    font-weight: 600;
    width: 100%;
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
.icon { font-size: 1.125rem; }

/* Table */
.card {
    background-color: white;
    border-radius: 1.5rem;
    border: 1px solid var(--color-border, #e2e8f0);
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
}
.table-responsive {
    overflow-x: auto;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}
.data-table th {
    padding: 1rem 1.5rem;
    font-size: 0.625rem;
    font-weight: 700;
    color: white;
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
.hover-row:hover td {
    background-color: rgba(248, 250, 252, 0.8);
}
.text-right { text-align: right; }
.pr-6 { padding-right: 1.5rem; }

/* New UI Styles */
.detailed-header {
  background-color: white;
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  margin-bottom: 24px;
  display: flex !important; /* Force flex */
  justify-content: space-between;
  align-items: center;
}

.header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
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

.th-blue {
    background-color: #4f46e5 !important; /* Match primary button color or request */
    color: white !important;
}

/* Column Widths */
.td-name-width {
    min-width: 200px;
    max-width: 260px;
    width: 22%;
}

.td-gender-width {
    min-width: 120px;
}

/* Profile Column */
.profile-group {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.avatar-circle {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
}

.bg-blue { background: #e0e7ff; color: #4f46e5; }
.bg-purple { background: #f3e8ff; color: #9333ea; }
.bg-amber { background: #fef3c7; color: #d97706; }
.bg-emerald { background: #d1fae5; color: #059669; }
.bg-rose { background: #ffe4e6; color: #e11d48; }

.profile-text { display: flex; flex-direction: column; }
.text-name {
    font-weight: 700;
    color: #0f172a;
    font-size: 0.875rem;
    max-width: 240px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}
.text-role { font-size: 0.7rem; color: #94a3b8; font-weight: 500; margin-top: 0.125rem; }

/* Other Columns */
.text-nip { font-weight: 600; color: #334155; font-size: 0.8125rem; }
.text-nik { font-size: 0.625rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }

.text-gender { font-weight: 500; font-size: 0.8125rem; color: #475569; }

.text-dob-city { font-weight: 500; color: #475569; font-size: 0.8125rem; }
.text-dob-date { font-size: 0.65rem; font-weight: 700; color: #64748b; }

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    display: inline-block;
}
.badge-pns { background: #d1fae5; color: #047857; }
.badge-pppk { background: #e0e7ff; color: #4338ca; }
.badge-honorer { background: #fef3c7; color: #b45309; }
.badge-default { background: #f1f5f9; color: #64748b; }

.contact-list { display: flex; flex-direction: column; gap: 0.25rem; }
.contact-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #64748b; }

/* Account Column */
.account-info { display: flex; flex-direction: column; }
.password-display { 
    display: flex; 
    align-items: center; 
    gap: 0.5rem; 
    background: #f8fafc; 
    padding: 0.25rem 0.5rem; 
    border-radius: 0.5rem; 
    border: 1px solid #e2e8f0;
    width: fit-content;
}
.password-text { 
    font-family: monospace; 
    font-size: 0.8rem; 
    color: #334155; 
    font-weight: 600;
    min-width: 60px;
}
.btn-icon-xs {
    background: none;
    border: none;
    padding: 2px;
    cursor: pointer;
    color: #94a3b8;
    display: flex;
    align-items: center;
    border-radius: 4px;
    transition: color 0.2s;
}
.btn-icon-xs:hover { color: #475569; background: #e2e8f0; }

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

.icon-sm { font-size: 1.125rem; }

/* Footer Summary */
.pagination-footer {
    padding: 1.5rem;
    background-color: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
}
.showing-text {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
}

/* Empty State */
.empty-state { text-align: center; padding: 4rem; color: #94a3b8; }
.empty-icon { font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.5; }

/* App Footer */
.app-footer { margin-top: 3rem; text-align: center; padding-bottom: 2rem; }
.footer-separator { height: 1px; background: #e2e8f0; margin-bottom: 1.5rem; width: 100%; }
.footer-copy { font-size: 0.65rem; font-weight: 700; color: #94a3b8; letter-spacing: 0.2em; text-transform: uppercase; }

/* Modal */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background-color: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

/* New Teacher Modal Styles */
.teacher-modal {
    background: #ffffff;
    width: 100%;
    max-width: 768px;
    border-radius: 2.5rem;
    box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;
}
.teacher-modal-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}
.header-left { display: flex; align-items: center; gap: 1rem; }
.header-icon {
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
.modal-title { font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0; }
.modal-subtitle { font-size: 0.75rem; color: #94a3b8; margin-top: 0.2rem; }
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

.teacher-modal-body {
    padding: 1.5rem 2rem;
    overflow-y: auto;
    background: #ffffff;
}
.teacher-form { display: flex; flex-direction: column; gap: 2rem; }
.teacher-section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 1rem;
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
.step-orange { background: #fff7ed; color: #f97316; }
.step-emerald { background: #ecfdf3; color: #10b981; }

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem 1rem;
}
.span-2 { grid-column: span 2; }
.label-compact {
    font-size: 0.7rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    display: block;
    margin-bottom: 0.4rem;
}
.input-compact {
    width: 100%;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 0.7rem 0.9rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #0f172a;
    outline: none;
}
.input-compact:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    background: #ffffff;
}
.textarea-compact { min-height: 80px; resize: none; }
.error-state { border-color: #ef4444; background: #fef2f2; }
.error-text {
    font-size: 0.65rem;
    color: #ef4444;
    font-weight: 700;
    margin-top: 0.3rem;
    display: block;
    min-height: 0.9rem;
}
.helper-text {
    font-size: 0.65rem;
    color: #94a3b8;
    font-weight: 600;
    margin-top: 0.3rem;
    min-height: 0.9rem;
}

.teacher-modal-footer {
    padding: 1.25rem 2rem;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
.btn-primary-action {
    padding: 0.75rem 1.5rem;
    border-radius: 0.9rem;
    background: #4f46e5;
    color: #ffffff;
    border: none;
    font-weight: 800;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    box-shadow: 0 12px 20px -14px rgba(79, 70, 229, 0.5);
}
.btn-primary-action:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-primary-action.loading { background: #818cf8; }
.btn-cancel {
    padding: 0.75rem 1.5rem;
    border-radius: 0.9rem;
    font-weight: 800;
    font-size: 0.75rem;
    color: #64748b;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    cursor: pointer;
}
.btn-cancel:hover { background: #f1f5f9; }

/* Import Modal Styles */
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

/* Detail Modal */
.detail-modal {
    background: #ffffff;
    width: 100%;
    max-width: 860px;
    border-radius: 2.5rem;
    box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;
}
.detail-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}
.detail-body {
    padding: 1.5rem 2rem;
    background: #f8fafc;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.detail-hero {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #ffffff;
    border-radius: 1.5rem;
    border: 1px solid #e2e8f0;
    padding: 1rem 1.25rem;
}
.detail-avatar {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    background: #eef2ff;
    color: #4f46e5;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.detail-name { font-size: 1rem; font-weight: 800; color: #0f172a; }
.detail-meta { font-size: 0.75rem; color: #94a3b8; font-weight: 600; }
.detail-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}
.detail-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1.25rem;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
.detail-card h4 {
    font-size: 0.8rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.25rem;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    font-size: 0.75rem;
    color: #64748b;
}
.detail-row strong {
    color: #0f172a;
    font-weight: 700;
    text-align: right;
    max-width: 60%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.detail-footer {
    padding: 1.25rem 2rem;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
@media (max-width: 900px) {
    .detail-grid { grid-template-columns: 1fr; }
    .detail-row strong { max-width: 100%; }
}

@media (max-width: 640px) {
    .form-grid { grid-template-columns: 1fr; }
    .span-2 { grid-column: span 1; }
}


/* Premium Modal Styles */
.modal-panel-premium {
    background-color: white;
    width: 100%;
    max-width: 900px;
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    animation: slideUp 0.3s ease-out;
    overflow: hidden;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Modal Header Premium */
.modal-header-premium {
    background-color: #1e40af; /* primary color */
    padding: 1rem 1.5rem; /* Reduced padding */
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem; /* Reduced gap */
}

.header-icon-box {
    width: 2.5rem; /* Reduced size */
    height: 2.5rem; /* Reduced size */
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.header-text {
    display: flex;
    flex-direction: column;
}

.modal-heading-premium {
    font-size: 1.125rem; /* Reduced font size */
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: -0.025em;
    margin: 0;
    line-height: 1.2;
}

.modal-subheading-premium {
    font-size: 0.65rem; /* Reduced font size */
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
}

.close-btn {
    padding: 0.5rem;
    border-radius: 0.5rem;
    background: transparent;
    border: none;
    cursor: pointer;
    color: white;
    transition: background-color 0.2s;
}

.close-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

/* Modal Body */
.modal-body-premium {
    padding: 1.5rem; /* Reduced padding */
    overflow-y: auto;
    background-color: #f8fafc;
}

.form-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem; /* Reduced gap */
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.highlight-section {
    background-color: rgba(248, 250, 252, 0.5); /* bg-slate-50 equivalent */
    padding: 1.25rem;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.25rem;
}

.step-badge {
    width: 1.25rem; /* Reduced size */
    height: 1.25rem; /* Reduced size */
    border-radius: 50%;
    background-color: rgba(30, 64, 175, 0.1);
    color: #1e40af;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.625rem;
    font-weight: 900;
}

.section-title {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    white-space: nowrap;
}

.section-divider {
    height: 1px;
    flex: 1;
    background-color: #e2e8f0;
}

.span-full {
    grid-column: 1 / -1;
}

/* Password Wrapper */
.password-wrapper {
    position: relative;
}

.password-icon {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    cursor: pointer;
    font-size: 1.25rem;
}

/* Footer Action */
.form-footer-premium {
    display: flex;
    justify-content: flex-end; /* Right align */
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
    background-color: #f8fafc;
    gap: 1rem;
}

.btn-save-premium {
    padding: 0.75rem 1.5rem; /* Standard button padding */
    background-color: #1e40af;
    color: white;
    border-radius: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.75rem;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.2); /* Reduced shadow */
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-save-premium:hover {
    background-color: #1e3a8a;
    transform: translateY(-1px);
    box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.3);
}

.btn-save-premium:active {
    transform: scale(0.98);
}

.resize-none { resize: none; }
.icon-2xl { font-size: 1.5rem; }

/* Shared Form Utilities (Restored) */
.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
@media(min-width: 768px) { .form-grid-2 { grid-template-columns: 1fr 1fr; } }

.input-group { display: flex; flex-direction: column; gap: 0.5rem; }
.input-label { font-size: 0.875rem; font-weight: 700; color: #334155; }
.label-muted { color: #94a3b8; font-weight: 400; font-size: 0.8rem; }

.text-input, .select-input, .textarea-input {
    width: 100%;
    padding: 0.75rem 1rem;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    color: #0f172a;
    transition: all 0.2s;
}

.text-input:focus, .select-input:focus, .textarea-input:focus {
    outline: none;
    border-color: #1e40af;
    box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
}



/* Restored Base Modal & Buttons (for Delete Modal) */
.modal-panel {
    background: white;
    width: 100%;
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    max-height: 90vh;
}

.btn-secondary {
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 700;
    color: #475569;
    background: white;
    border: 1px solid #e2e8f0;
    cursor: pointer;
}


/* Error State */
.border-red-500 { border-color: #ef4444 !important; }
.text-red-500 { color: #ef4444; }
.text-xs { font-size: 0.75rem; line-height: 1rem; }

/* Modal XS (Delete) */
.modal-xs { 
    max-width: 400px !important; 
    width: 90%; /* Responsive fallback */
    margin: 0 auto;
}
.center-content { text-align: center; }
.modal-body-center { padding: 2rem 1.5rem 1rem; }

.danger-icon-box {
    width: 4rem;
    height: 4rem;
    background: #fef2f2;
    border-radius: 50%;
    color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.icon-2xl { font-size: 2rem; }

.modal-heading-center { font-size: 1.25rem; font-weight: 900; color: #0f172a; margin-bottom: 0.5rem; line-height: 1.3; }
.modal-text-center { font-size: 0.875rem; color: #64748b; margin-bottom: 1.5rem; }

/* Import Modal Styles */
.error-message {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    background-color: #fef2f2;
    padding: 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid #fee2e2;
}
.modal-footer-center { padding: 1.5rem; display: flex; justify-content: center; gap: 1rem; background: #f8fafc; border-top: 1px solid #f1f5f9; }

.btn-secondary {
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 700;
    color: #475569;
    background: white;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-secondary:hover { background: #f1f5f9; color: #334155; }

.btn-danger {
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 700;
    color: white;
    background: #ef4444;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
    transition: all 0.2s;
}
.btn-danger:hover { background: #dc2626; }

/* Base Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    z-index: 2000;
    animation: fadeIn 0.2s ease-out;
}

.modal-content {
    background: white;
    width: 100%;
    max-width: 550px;
    border-radius: 1.25rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    animation: slideUp 0.3s ease-out;
}

.modal-header {
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 800;
    color: #0f172a;
}

.btn-close {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    color: #64748b;
    border: none;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-close:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.modal-body-premium {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.modal-footer {
    padding: 1.25rem 1.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Improved Import Modal Styles */
.import-step { display: flex; flex-direction: column; gap: 0.75rem; }
.step-header { display: flex; align-items: center; gap: 0.75rem; }
.step-number { 
    background: #e0e7ff; color: #4338ca; 
    width: 1.5rem; height: 1.5rem; 
    border-radius: 50%; display: flex; 
    align-items: center; justify-content: center; 
    font-weight: 800; font-size: 0.75rem; 
}
.step-title { font-weight: 700; color: #334155; font-size: 0.9rem; }
.step-desc { font-size: 0.8rem; color: #64748b; margin-left: 2.25rem; }

.btn-download-template {
    margin-left: 2.25rem;
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: white; border: 1px solid #cbd5e1;
    border-radius: 0.5rem; color: #475569;
    font-size: 0.8rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
    width: fit-content;
}
.btn-download-template:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }

.file-upload-box {
    margin-left: 2.25rem;
    border: 2px dashed #cbd5e1;
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #f8fafc;
}
.file-upload-box:hover { border-color: #94a3b8; background: #f1f5f9; }
.file-upload-box.has-file { border-color: #10b981; background: #ecfdf5; border-style: solid; }

.hidden-input { display: none; }
.upload-icon { font-size: 2rem; color: #94a3b8; margin-bottom: 0.5rem; }
.upload-text { font-size: 0.8rem; color: #64748b; font-weight: 500; }
.file-name { color: #059669; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }

.my-4 { margin-top: 1rem; margin-bottom: 1rem; }

/* Modal Button Styles */
.btn-cancel {
    padding: 0.7rem 1.25rem;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.875rem;
    color: #64748b;
    background: #f1f5f9;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #e2e8f0;
    color: #475569;
}

.btn-save {
    padding: 0.7rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.875rem;
    color: white;
    background: #1e40af;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.2);
}

.btn-save:hover:not(:disabled) {
    background: #1e3a8a;
    transform: translateY(-1px);
    box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.3);
}

.btn-save:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    opacity: 0.7;
    box-shadow: none;
}

.icon-spin {
    animation: spin 1s linear infinite;
}

</style>
