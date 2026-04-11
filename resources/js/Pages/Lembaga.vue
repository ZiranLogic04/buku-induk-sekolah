<template>
  <div class="institution-page">
    <div class="header-card">
      <div class="header-flex">
        <div>
          <h1 class="page-title">Pengaturan Lembaga</h1>
          <p class="page-subtitle">Kelola konfigurasi utama dan data lembaga dalam satu panel efisien.</p>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <!-- Left Column: Logo & Config -->
      <div class="col-left">
        <!-- Logo Upload -->
        <div class="card card-sticky">
          <div class="logo-upload-section">
            <div class="upload-wrapper group" @click="$refs.logoInput.click()">
              <div class="upload-box" :class="{ 'has-img': logoUrl }">
                <img v-if="logoUrl" :src="logoUrl" class="logo-preview-img" />
                <template v-else>
                  <span class="material-symbols-outlined upload-icon">upload_file</span>
                  <span class="upload-label">Logo Lembaga</span>
                </template>
              </div>
              <button class="edit-btn">
                <span class="material-symbols-outlined edit-icon">edit</span>
              </button>
              <button v-if="logoUrl && !uploadStatus" class="delete-btn" @click.stop="deleteLogo" title="Hapus Logo">
                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
              </button>
              <div v-if="uploadStatus" class="upload-progress-container">
                <div class="progress-bar-fill" :style="{ width: uploadProgress + '%' }"></div>
              </div>
            </div>
            <p v-if="uploadStatus" class="upload-status-hint">Mengunggah...</p>
            <p v-else class="upload-hint">512x512 PNG Direkomendasikan</p>
            <input type="file" ref="logoInput" class="hidden" @change="onLogoChange" accept="image/*" />
          </div>
          
          <div class="divider"></div>
          
          <div class="config-section">
            <div class="config-box">
              <span class="config-header">Core Configuration</span>
              <div class="form-stack">
                <div class="form-group">
                  <label class="form-label-compact">Tahun Ajaran</label>
                  <div class="input-row">
                    <select v-model="form.tahun_ajaran" class="form-input-compact flex-1">
                      <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                    </select>
                    <button class="add-btn" type="button" @click="showYearModal = true" title="Tambah Tahun Ajaran">
                      <span class="material-symbols-outlined icon-lg">add_circle</span>
                    </button>
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="form-label-compact">Semester</label>
                  <select v-model="form.semester" class="form-input-compact">
                    <option>Ganjil</option>
                    <option>Genap</option>
                  </select>
                </div>
                
                <div class="form-group">
                  <label class="form-label-compact">Jurusan Aktif</label>
                  <div class="input-row">
                    <select v-model="form.jurusan" class="form-input-compact flex-1">
                      <option v-for="j in majors" :key="j" :value="j">{{ j }}</option>
                    </select>
                    <button class="add-btn" type="button" @click="showMajorModal = true" title="Tambah Jurusan">
                      <span class="material-symbols-outlined icon-lg">add_circle</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            

          </div>
        </div>
      </div>

      <!-- Right Column: Identity & Headmaster -->
      <div class="col-right">
        <!-- Identity Card -->
        <div class="card">
          <div class="card-header">
            <div class="header-icon-box bg-blue">
              <span class="material-symbols-outlined">badge</span>
            </div>
            <h2 class="card-title">Identitas Lembaga</h2>
          </div>
          <div class="card-body">
            <div class="form-grid">
              <div class="form-group span-2">
                <label class="form-label-compact">Nama Lembaga</label>
                <input v-model="form.nama" class="form-input-compact" :class="{ 'has-error': errors.nama }" type="text" placeholder="Contoh: SMA Negeri 1 Jakarta"/>
                <span v-if="errors.nama" class="error-msg">
                    <span class="material-symbols-outlined" style="font-size: 14px;">warning</span>
                    {{ errors.nama[0] }}
                </span>
              </div>
              <div class="form-group">
                <label class="form-label-compact">Jenis Lembaga</label>
                <select v-model="form.jenis" class="form-input-compact" @change="resetJenjang">
                  <option>Sekolah</option>
                  <option>Madrasah</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label-compact">Status</label>
                <select v-model="form.status" class="form-input-compact">
                  <option>Negeri</option>
                  <option>Swasta</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label-compact">NPSN</label>
                <input v-model="form.npsn" class="form-input-compact" :class="{ 'has-error': errors.npsn }" type="text" placeholder="Nomor Pokok Sekolah Nasional"/>
                <span v-if="errors.npsn" class="error-msg">
                    <span class="material-symbols-outlined" style="font-size: 14px;">warning</span>
                    {{ errors.npsn[0] }}
                </span>
              </div>
              <div class="form-group">
                <label class="form-label-compact">{{ dynamicLabelNss }}</label>
                <input v-model="form.nss" class="form-input-compact" :class="{ 'has-error': errors.nss }" type="text" :placeholder="'Masukkan ' + dynamicLabelNss"/>
                <span v-if="errors.nss" class="error-msg">
                    <span class="material-symbols-outlined" style="font-size: 14px;">warning</span>
                    {{ errors.nss[0] }}
                </span>
              </div>
              <div class="form-group span-2">
                <label class="form-label-compact">Alamat Lengkap</label>
                <textarea v-model="form.alamat" class="form-input-compact resize-none" :class="{ 'has-error': errors.alamat }" rows="2"></textarea>
                <span v-if="errors.alamat" class="error-msg">
                    <span class="material-symbols-outlined" style="font-size: 14px;">warning</span>
                    {{ errors.alamat[0] }}
                </span>
              </div>
              <div class="form-group">
                <label class="form-label-compact">Jenjang Pendidikan</label>
                <select v-model="form.jenjang" class="form-input-compact">
                  <option v-for="level in dynamicLevels" :key="level" :value="level">{{ level }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label-compact">Kurikulum</label>
                <select v-model="form.kurikulum" class="form-input-compact">
                  <option value="Kurikulum Merdeka">Kurikulum Merdeka</option>
                  <option value="Kurikulum 2013">Kurikulum 2013</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Headmaster Card -->
        <div class="card">
          <div class="card-header">
            <div class="header-icon-box bg-indigo">
              <span class="material-symbols-outlined">person_pin</span>
            </div>
            <h2 class="card-title">Kepala Sekolah</h2>
          </div>
          <div class="card-body">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label-compact">Nama Kepala Lembaga</label>
                <input v-model="form.nama_kepala_sekolah" class="form-input-compact" :class="{ 'has-error': errors.nama_kepala_sekolah }" type="text" placeholder="Nama Lengkap & Gelar"/>
                <span v-if="errors.nama_kepala_sekolah" class="error-msg">
                    <span class="material-symbols-outlined" style="font-size: 14px;">warning</span>
                    {{ errors.nama_kepala_sekolah[0] }}
                </span>
              </div>
              <div class="form-group">
                <label class="form-label-compact">NIP Kepala Lembaga</label>
                <input v-model="form.nip_kepala_sekolah" class="form-input-compact" :class="{ 'has-error': errors.nip_kepala_sekolah }" type="text" placeholder="NIP / NIY"/>
                <span v-if="errors.nip_kepala_sekolah" class="error-msg">
                    <span class="material-symbols-outlined" style="font-size: 14px;">warning</span>
                    {{ errors.nip_kepala_sekolah[0] }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="save-wrapper">
          <button class="btn-save" @click="submit" :disabled="form.processing">
            <span class="material-symbols-outlined icon-sm">save</span>
            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </div>
    </div>
    <!-- Modals -->
    <div v-if="showYearModal" class="modal-overlay" @click.self="showYearModal = false">
      <div class="modal-content">
        <h3 class="modal-title">Tambah Tahun Ajaran</h3>
        <div class="modal-body-p">
          <div class="form-group">
            <label class="form-label-compact">Tahun Ajaran</label>
            <input v-model="newYear" class="form-input-compact" type="text" placeholder="Contoh: 2025/2026" @keyup.enter="addYear" />
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn-cancel" @click="showYearModal = false">Batal</button>
          <button class="btn-confirm" @click="addYear">Tambah</button>
        </div>
      </div>
    </div>

    <div v-if="showMajorModal" class="modal-overlay" @click.self="showMajorModal = false">
      <div class="modal-content">
        <h3 class="modal-title">Tambah Jurusan</h3>
        <div class="modal-body-p">
          <div class="form-group">
            <label class="form-label-compact">Nama Jurusan</label>
            <input v-model="newMajor" class="form-input-compact" type="text" placeholder="Contoh: Akuntansi" @keyup.enter="addMajor" />
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn-cancel" @click="showMajorModal = false">Batal</button>
          <button class="btn-confirm" @click="addMajor">Tambah</button>
        </div>
      </div>
    </div>

    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-content-danger">
        <div class="modal-icon-wrapper-danger">
          <span class="material-symbols-outlined modal-icon-danger">warning</span>
        </div>
        <h3 class="modal-title-danger">Hapus Logo Lembaga?</h3>
        <p class="modal-desc-danger">
          Tindakan ini akan <strong>menghapus permanen</strong> file logo dari server. Apakah Anda yakin ingin melanjutkan?
        </p>
        <div class="modal-actions-danger">
          <button class="btn-cancel-danger" @click="showDeleteModal = false">Batal</button>
          <button class="btn-confirm-danger" @click="confirmDeleteLogo">
            <span v-if="processingDelete">Menghapus...</span>
            <span v-else>Ya, Hapus Logo</span>
          </button>
        </div>
      </div>
    </div>

    
    <!-- Premium Toast -->
    <Toast 
        :show="toast.show" 
        :type="toast.type" 
        :title="toast.title" 
        :message="toast.message" 
        @close="toast.show = false" 
    />
  </div>
</template>

<script setup>
import { reactive, onMounted, ref, computed } from 'vue';
import axios from 'axios';
import Toast from '@/Components/Toast.vue';

const form = reactive({
    tahun_ajaran: '2024/2025',
    semester: 'Ganjil',
    jurusan: 'Umum',
    nama: '',
    jenis: 'Sekolah',
    status: 'Negeri',
    npsn: '',
    nss: '',
    alamat: '',
    jenjang: 'SMA / MA',
    kurikulum: 'Kurikulum Merdeka',
    nama_kepala_sekolah: '',
    nip_kepala_sekolah: '',
    logo: null,
    processing: false
});

const errors = ref({});

const logoUrl = ref(null);
const uploadStatus = ref(false);
const uploadProgress = ref(0);

const years = ref([]);
const majors = ref([]);

// Toast State
const toast = reactive({
    show: false,
    type: 'success',
    title: '',
    message: ''
});

const showToast = (type, title, message) => {
    toast.type = type;
    toast.title = title;
    toast.message = message;
    toast.show = true;
};

const showYearModal = ref(false);
const newYear = ref('');
const showMajorModal = ref(false);
const newMajor = ref('');

// Dynamic Labels
const dynamicLabelNss = computed(() => form.jenis === 'Madrasah' ? 'NSM' : 'NSS');
const dynamicLevels = computed(() => {
    if (form.jenis === 'Madrasah') {
        return ['MI', 'MTs', 'MA'];
    }
    return ['SD', 'SMP', 'SMA', 'SMK'];
});

const resetJenjang = () => {
    form.jenjang = dynamicLevels.value[0];
};

const fetchData = async () => {
    try {
        const response = await axios.get('/api/admin/lembaga');
        const data = response.data;
        Object.assign(form, {
            tahun_ajaran: data.lembaga.tahun_ajaran,
            semester: data.lembaga.semester,
            nama: data.lembaga.nama,
            jenis: data.lembaga.jenis,
            status: data.lembaga.status,
            npsn: data.lembaga.npsn || '',
            nss: data.lembaga.nss || '',
            alamat: data.lembaga.alamat || '',
            jenjang: data.lembaga.jenjang,
            kurikulum: data.lembaga.kurikulum || 'Kurikulum Merdeka',
            nama_kepala_sekolah: data.lembaga.nama_kepala_sekolah || '',
            nip_kepala_sekolah: data.lembaga.nip_kepala_sekolah || '',
            jurusan: data.lembaga.jurusan || 'Umum',
        });
        
        years.value = data.tahun_ajarans;
        // Hardcode 'Umum' to ensure it exists without manual addition
        const uniqueMajors = new Set(['Umum', ...data.jurusans]);
        majors.value = Array.from(uniqueMajors);

        if (data.lembaga.logo_path) {
            logoUrl.value = `/storage/${data.lembaga.logo_path}`;
        }
    } catch (error) {
        console.error('Gagal mengambil data lembaga:', error);
    }
};

onMounted(() => {
    fetchData();
});

const submit = async () => {
    form.processing = true;
    
    // Create FormData for saving other data (not auto-logo)
    const formData = new FormData();
    for (const key in form) {
        if (key !== 'processing' && key !== 'logo' && form[key] !== null) {
            formData.append(key, form[key]);
        }
    }

    try {
        await axios.post('/api/admin/lembaga', formData);
        showToast('success', 'Berhasil Disimpan', 'Data lembaga berhasil disimpan ke database.');
        errors.value = {}; // Clear errors on success
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
            showToast('error', 'Validasi Gagal', 'Harap periksa kembali inputan Anda.');
        } else {
            console.error('Gagal menyimpan data:', error);
            const status = error.response?.status;
            const errorMessage = status && status >= 500
              ? 'Terjadi kesalahan pada server. Silakan coba lagi.'
              : (error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data lembaga.');
            showToast('error', 'Gagal Menyimpan', errorMessage);
        }
    } finally {
        form.processing = false;
    }
};

const onLogoChange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    uploadStatus.value = true;
    uploadProgress.value = 30;

    const formData = new FormData();
    formData.append('logo', file);

    try {
        const response = await axios.post('/api/admin/lembaga/logo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (progressEvent) => {
                uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            }
        });
        logoUrl.value = response.data.logo_url;
        uploadProgress.value = 100;
        setTimeout(() => { uploadStatus.value = false; }, 800);
        showToast('success', 'Logo Diperbarui', 'Logo lembaga berhasil diunggah.');
    } catch (error) {
        console.error('Gagal upload logo:', error);
        const errorMessage = error.response?.data?.message || 'Gagal mengunggah logo. Pastikan format sesuai.';
        showToast('error', 'Gagal Upload', errorMessage);
        uploadStatus.value = false;
    }
};


const showDeleteModal = ref(false);

const deleteLogo = () => {
    showDeleteModal.value = true;
};

const processingDelete = ref(false);

const confirmDeleteLogo = async () => {
    processingDelete.value = true;
    try {
        await axios.delete('/api/admin/lembaga/logo');
        logoUrl.value = null;
        showDeleteModal.value = false;
        showToast('success', 'Logo Dihapus', 'Logo lembaga berhasil dihapus.');
    } catch (error) {
        console.error('Gagal menghapus logo:', error);
        const errorMessage = error.response?.data?.message || 'Gagal menghapus logo.';
        showToast('error', 'Gagal', errorMessage);
        showDeleteModal.value = false;
    } finally {
        processingDelete.value = false;
    }
};

const addYear = async () => {
    if (!newYear.value) {
        showToast('warning', 'Input Kosong', 'Silakan masukkan tahun ajaran terlebih dahulu.');
        return;
    }

    try {
        await axios.post('/api/admin/lembaga/tahun-ajaran', { tahun_ajaran: newYear.value });
        years.value.push(newYear.value);
        form.tahun_ajaran = newYear.value;
        showToast('success', 'Berhasil', `Tahun ajaran ${newYear.value} berhasil ditambahkan.`);
        newYear.value = '';
        showYearModal.value = false;
    } catch (error) {
        console.error('Gagal menambah tahun ajaran:', error);
        const errorMessage = error.response?.data?.message || 'Gagal menambahkan tahun ajaran.';
        showToast('error', 'Gagal', errorMessage);
    }
};

const addMajor = async () => {
    if (!newMajor.value) {
        showToast('warning', 'Input Kosong', 'Silakan masukkan nama jurusan terlebih dahulu.');
        return;
    }

    try {
        await axios.post('/api/admin/lembaga/jurusan', { jurusan: newMajor.value });
        majors.value.push(newMajor.value);
        form.jurusan = newMajor.value;
        showToast('success', 'Berhasil', `Jurusan ${newMajor.value} berhasil ditambahkan.`);
        newMajor.value = '';
        showMajorModal.value = false;
    } catch (error) {
        console.error('Gagal menambah jurusan:', error);
        const errorMessage = error.response?.data?.message || 'Gagal menambahkan jurusan.';
        showToast('error', 'Gagal', errorMessage);
    }
};
</script>

<style scoped>
@import url('@/../css/danger-modal.css');

.institution-page {
    width: 100%;
    padding: 1.5rem;
}

@media (min-width: 768px) {
    .institution-page {
        padding: 2rem;
    }
}

/* Header Card */
.header-card {
    background-color: white;
    padding: 1.5rem;
    border-radius: 1.5rem; /* rounded-3xl */
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
    border: 1px solid var(--color-border);
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    .header-card { padding: 2rem; }
}

.page-title {
    font-size: 1.5rem; /* 2xl */
    font-weight: 900; /* font-black */
    color: var(--color-text-main);
    letter-spacing: -0.025em; /* tracking-tight */
    margin-bottom: 0.25rem;
}

.page-subtitle {
    color: var(--color-text-muted);
    font-size: 0.875rem; /* text-sm */
    font-weight: 500;
}

/* Grid Layout */
.content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 1024px) {
    .content-grid {
        grid-template-columns: repeat(10, 1fr);
    }
    .col-left {
        grid-column: span 3;
    }
    .col-right {
        grid-column: span 7;
    }
}

/* Columns */
.col-left {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.col-right {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Base Card */
.card {
    background-color: white;
    border-radius: 1.5rem; /* rounded-3xl */
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    border: 1px solid var(--color-border);
    overflow: hidden;
}

.card-sticky {
    position: sticky;
    top: 1.5rem;
    padding: 1.5rem;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #f1f5f9; /* border-slate-100 */
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-title {
    font-size: 1.125rem; /* text-lg */
    font-weight: 700;
    color: var(--color-text-main);
}

.card-body {
    padding: 2rem;
}

/* Icon Boxes */
.header-icon-box {
    width: 2.5rem; /* w-10 */
    height: 2.5rem;
    border-radius: 0.75rem; /* rounded-xl */
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-blue {
    background-color: #eff6ff; /* blue-50 */
    color: #2563eb; /* blue-600 */
}

.bg-indigo {
    background-color: #eef2ff; /* indigo-50 */
    color: #4f46e5; /* indigo-600 */
}

/* Logo Upload Section */
.logo-upload-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 1.5rem;
}

.upload-wrapper {
    position: relative;
    cursor: pointer;
}

/* Group hover effect implementation needed careful structure or JS, simplified here */
.upload-wrapper:hover .upload-box {
    border-color: var(--color-primary);
    background-color: rgba(30, 64, 175, 0.05);
}

.upload-wrapper:hover .edit-btn {
    transform: scale(1);
    opacity: 1;
}

.upload-wrapper:hover .upload-icon, .upload-wrapper:hover .upload-label {
    color: var(--color-primary);
}

.upload-box {
    width: 8rem; /* w-32 */
    height: 8rem;
    border-radius: 1rem; /* rounded-2xl */
    background-color: #f8fafc; /* bg-slate-50 */
    border: 2px dashed #cbd5e1; /* border-slate-300 */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    transition: all 0.2s;
}

.upload-icon {
    font-size: 2.25rem; /* text-4xl */
    color: #94a3b8; /* text-slate-400 */
    margin-bottom: 0.5rem;
}

.upload-label {
    font-size: 0.625rem; /* text-[10px] */
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.edit-btn {
    position: absolute;
    bottom: -0.5rem;
    right: -0.5rem;
    width: 2rem;
    height: 2rem;
    background-color: var(--color-primary);
    border-radius: 0.5rem;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: none;
    transform: scale(0);
    transition: transform 0.2s;
    transition: transform 0.2s;
}

.delete-btn {
    position: absolute;
    top: -0.5rem;
    right: -0.5rem;
    width: 2rem;
    height: 2rem;
    background-color: #ef4444; /* red-500 */
    border-radius: 0.5rem;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: none;
    cursor: pointer;
    transform: scale(0);
    transition: transform 0.2s;
    z-index: 10;
}

.upload-wrapper:hover .delete-btn {
    transform: scale(1);
}

.upload-hint {
    margin-top: 1rem;
    font-size: 0.625rem;
    color: #94a3b8; /* text-slate-400 */
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    text-align: center;
}

.error-msg {
    color: #ef4444; /* red-500 */
    font-size: 0.75rem; /* text-xs */
    margin-top: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-weight: 500;
}

.form-input-compact.has-error {
    border-color: #ef4444; /* red-500 */
    background-color: #fef2f2; /* red-50 */
}

.form-input-compact.has-error:focus {
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2); /* red ring */
}

.divider {
    height: 1px;
    width: 100%;
    background-color: #f1f5f9; /* bg-slate-100 */
    margin-bottom: 1.5rem;
}

/* Config Section */
.config-section {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.config-box {
    background-color: rgba(248, 250, 252, 0.5); /* bg-slate-50/50 */
    padding: 1rem;
    border-radius: 1rem;
    border: 1px solid #f1f5f9; /* border-slate-100 */
}

.config-header {
    display: block;
    font-size: 0.625rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 1rem;
}

.form-stack {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label-compact {
    display: block;
    font-size: 0.6875rem; /* 11px */
    font-weight: 700;
    color: #64748b; /* text-slate-500 */
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}

.input-row {
    display: flex;
    gap: 0.5rem;
    align-items: center; /* Ensure vertical alignment */
}

.form-input-compact {
    width: 100%;
    padding: 0.5625rem 0.75rem; /* adjusted padding to match approx 40px height with border */
    background-color: #f8fafc; /* bg-slate-50 */
    border: 1px solid #e2e8f0; /* border-slate-200 */
    border-radius: 0.5rem; /* rounded-lg */
    font-size: 0.875rem; /* text-sm */
    line-height: 1.25rem;
    color: var(--color-text-main);
    transition: all 0.2s;
    outline: none;
    height: 2.5rem; /* Explicit height to match buttons */
}

.form-input-compact:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.2);
}

.resize-none { resize: none; }
.flex-1 { flex: 1; }

.add-btn {
    width: 2.5rem;
    height: 2.5rem;
    background-color: rgba(30, 64, 175, 0.1);
    color: var(--color-primary);
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.add-btn:hover {
    background-color: var(--color-primary);
    color: white;
}

.icon-lg { font-size: 1.25rem; }

.save-wrapper {
    padding-top: 0.5rem;
}

.btn-save {
    width: 100%;
    padding: 0.875rem;
    background-color: var(--color-primary);
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    border-radius: 0.75rem; /* rounded-xl */
    border: none;
    cursor: pointer;
    box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: background-color 0.2s;
}

.btn-save:hover {
    background-color: var(--color-primary-dark);
}

.icon-sm { font-size: 0.875rem; }

/* Form Grid for Main Content */
.form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .form-grid {
        grid-template-columns: repeat(2, 1fr);
        column-gap: 2rem;
    }
    .span-2 {
        grid-column: span 2;
    }
}
</style>



<style scoped>
/* Additive Styles for Features */
.header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.logo-preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Restored Hover Effects via existing classes */
.upload-wrapper:hover .edit-btn {
    transform: scale(1);
    opacity: 1;
}

.upload-wrapper:hover .upload-box {
    border-color: var(--color-primary);
    background-color: rgba(30, 64, 175, 0.05); /* Restore subtle bg change */
}

/* Progress Bar */
.upload-progress-container {
    position: absolute;
    bottom: -1.5rem;
    left: 0;
    width: 100%;
    height: 0.25rem;
    background-color: #e2e8f0;
    border-radius: 0.125rem;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background-color: var(--color-primary);
    transition: width 0.3s ease;
}

.upload-status-hint {
    margin-top: 2rem;
    font-size: 0.625rem;
    color: var(--color-primary);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    text-align: center;
}

/* Modals */
.modal-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
    backdrop-filter: blur(2px);
}

.modal-content {
    background-color: white;
    border-radius: 1.5rem;
    padding: 2rem;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalPop {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--color-text-main);
    margin-bottom: 1.5rem;
    text-align: center;
}

.modal-body-p {
    margin-bottom: 2rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn-cancel {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    color: #64748b;
    background: transparent;
    border: none;
    cursor: pointer;
}

.btn-cancel:hover { background-color: #f1f5f9; }

.btn-confirm {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    color: white;
    background-color: var(--color-primary);
    border: none;
    cursor: pointer;
}

.btn-confirm:hover { background-color: var(--color-primary-dark); }
</style>
