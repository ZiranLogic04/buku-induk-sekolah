<template>
  <div class="page-container">
    <!-- Title Section -->
    <div class="page-header header-blue">
      <div class="header-content">
        <h1 class="page-title">Manajemen Kelas</h1>
        <p class="page-subtitle">Kelola data kelas, tingkat, dan penugasan wali kelas.</p>
      </div>
      <div class="header-actions">
        <button @click="openCopyModal" class="btn btn-outline btn-add">
          <span class="material-symbols-outlined">content_copy</span>
          Salin Kelas
        </button>
        <button @click="openModal" class="btn btn-primary btn-add">
          <span class="material-symbols-outlined">add_circle</span>
          Tambah Kelas
        </button>
      </div>
    </div>

      <!-- Table Section -->
      <div class="table-card">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th class="th-nama">Nama Kelas</th>
                <th>Tingkat</th>
                <th>Jurusan</th>
                <th>Jumlah Siswa</th>
                <th>Wali Kelas</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredKelas" :key="item.id" class="hover-row group">
                <td class="td-nama">
                  <div class="flex-center gap-md">
                    <div class="icon-box">
                      <span class="material-symbols-outlined icon-lg">domain</span>
                    </div>
                    <div>
                      <p class="item-title">{{ item.nama }}</p>
                      <p class="item-subtitle">Ruang {{ item.ruangan || '-' }}</p>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="flex-center gap-sm">
                    <div class="circle-icon">
                      <span class="material-symbols-outlined icon-sm">school</span>
                    </div>
                    <span class="item-text">Kelas {{ item.tingkat }}</span>
                  </div>
                </td>
                <td>
                  <div class="flex-center gap-sm">
                    <div class="circle-icon">
                      <span class="material-symbols-outlined icon-sm">explore</span>
                    </div>
                    <span class="item-text">{{ item.jurusan?.nama || '-' }}</span>
                  </div>
                </td>
                <td>
                  <div class="flex-center gap-sm">
                    <div class="circle-icon">
                      <span class="material-symbols-outlined icon-sm">groups</span>
                    </div>
                    <span class="item-text">{{ item.siswa_count ?? 0 }}</span>
                  </div>
                </td>
                <td>
                  <div class="flex-center gap-md">
                    <div class="avatar-circle">
                      {{ getWaliKelasInitials(item.wali_kelas_id) }}
                    </div>
                    <div>
                      <p class="wk-name">{{ getWaliKelasName(item.wali_kelas_id) }}</p>
                      <p class="wk-nip">{{ getWaliKelasNIP(item.wali_kelas_id) }}</p>
                    </div>
                  </div>
                </td>
                <td class="text-right">
                  <div class="action-buttons">
                     <button @click="editKelas(item)" class="btn-icon btn-edit">
                      <span class="material-symbols-outlined">edit</span>
                    </button>
                    <button @click="deleteKelas(item.id)" class="btn-icon btn-delete">
                      <span class="material-symbols-outlined">delete</span>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredKelas.length === 0">
                 <td colspan="6" class="empty-state">
                   <div class="empty-wrap">
                     <div class="empty-icon">
                       <span class="material-symbols-outlined">domain</span>
                     </div>
                     <div>
                       <p class="empty-title">Belum ada data kelas</p>
                       <p class="empty-desc">Mulai dengan menambahkan kelas baru untuk tahun ajaran ini.</p>
                     </div>
                     <button class="btn btn-primary btn-add" @click="openModal">
                       <span class="material-symbols-outlined">add_circle</span>
                       Tambah Kelas
                     </button>
                   </div>
                 </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination-footer">
          <p class="pagination-info">Menampilkan <span class="text-highlight">{{ filteredKelas.length }}</span> data</p>
        </div>
      </div>

      <!-- Modal Form -->
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="class-modal">
          <header class="class-modal-header compact">
            <div class="class-modal-title">
              <div class="title-icon">
                <span class="material-symbols-outlined">add_card</span>
              </div>
              <div>
                <h3>{{ isEdit ? 'Edit Data Kelas' : 'Master Data Kelas' }}</h3>
                <p>{{ isEdit ? 'Perbarui data kelas yang sudah ada.' : 'Input data lengkap pendaftaran kelas baru.' }}</p>
              </div>
            </div>
            <button class="close-icon" @click="closeModal" aria-label="Tutup">
              <span class="material-symbols-outlined">close</span>
            </button>
          </header>

          <div class="class-modal-body">
            <p class="year-hint">Input otomatis untuk Tahun Ajaran: <strong>{{ displayTahunAjaran }}</strong></p>

            <div class="modal-split">
              <div class="split-col">
                <div class="section-title">
                  <span class="step-badge">1</span>
                  Informasi Dasar Kelas
                </div>

                <div class="form-grid single">
                  <div class="form-group full">
                <label class="form-label">Nama Kelas</label>
                <input v-model="form.nama" class="form-input" type="text" placeholder="Contoh: X MIPA 1" />
                  </div>

                  <div class="form-group">
                    <label class="form-label">Tingkat</label>
                    <select v-model="form.tingkat" class="form-input">
                      <option v-for="i in tingkatOptions" :key="i" :value="i">Kelas {{ i }}</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="form-label">Jurusan</label>
                    <select v-model="form.jurusan_id" class="form-input">
                      <option v-for="jurusan in jurusans" :key="jurusan.id" :value="jurusan.id">
                        {{ jurusan.nama }}
                      </option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="form-label">Kode Ruangan</label>
                    <input v-model="form.ruangan" class="form-input" type="text" placeholder="Contoh: R.101" />
                  </div>
                </div>
              </div>

              <div class="split-col">
                <div class="section-title">
                  <span class="step-badge alt">2</span>
                  Penugasan Personil
                </div>

                <div class="form-grid single">
                  <div class="form-group full">
                    <label class="form-label">Wali Kelas</label>
                    <select v-model="form.wali_kelas_id" class="form-input">
                      <option :value="null">-- Pilih Wali Kelas --</option>
                      <option v-for="guru in gurus" :key="guru.id" :value="guru.id">
                        {{ guru.nama }}
                      </option>
                    </select>
                    <p class="helper-text">Pilih wali kelas jika sudah ditentukan.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <footer class="class-modal-footer">
            <button class="btn btn-secondary" @click="closeModal">Batal</button>
            <button class="btn btn-primary" @click="submitForm">
              <span class="material-symbols-outlined">save</span>
              {{ isEdit ? 'Simpan Perubahan' : 'Simpan Data Kelas' }}
            </button>
          </footer>
        </div>
      </div>

      <!-- Copy Classes Modal -->
      <div v-if="showCopyModal" class="modal-overlay" @click.self="closeCopyModal">
        <div class="class-modal" style="max-width: 500px;">
          <header class="class-modal-header compact">
            <div class="class-modal-title">
              <div class="title-icon" style="background: #10b981;">
                <span class="material-symbols-outlined">content_copy</span>
              </div>
              <div>
                <h3>Salin Kelas</h3>
                <p>Duplikat struktur kelas ke tahun ajaran baru.</p>
              </div>
            </div>
            <button class="close-icon" @click="closeCopyModal">
              <span class="material-symbols-outlined">close</span>
            </button>
          </header>

          <div class="class-modal-body">
            <div class="form-group full">
              <label class="form-label">Tahun Ajaran Sumber (Asal)</label>
              <select v-model="copyForm.source_year_id" class="form-input">
                <option value="">-- Pilih Tahun Sumber --</option>
                <option v-for="ta in tahunAjarans" :key="ta.id" :value="ta.id">
                  {{ ta.nama }}
                </option>
              </select>
            </div>

            <div class="form-group full" style="margin-top: 1rem;">
              <label class="form-label">Tahun Ajaran Target (Tujuan)</label>
              <select v-model="copyForm.target_year_id" class="form-input">
                <option v-for="ta in tahunAjarans" :key="ta.id" :value="ta.id">
                  {{ ta.nama }}
                </option>
              </select>
            </div>
            
            <div class="hint-box" style="margin-top: 1.5rem; padding: 1rem; background: #f0fdf4; border-radius: 1rem; border: 1px solid #bbf7d0;">
                <p style="font-size: 0.75rem; color: #166534; font-weight: 600; line-height: 1.4;">
                    <strong>Info:</strong> Kelas dengan nama yang sama di tahun target tidak akan diduplikat (skip).
                </p>
            </div>
          </div>

          <footer class="class-modal-footer">
            <button class="btn btn-secondary" @click="closeCopyModal">Batal</button>
            <button class="btn btn-primary" style="background: #10b981; color: white;" @click="submitCopy" :disabled="copying">
              <span class="material-symbols-outlined">{{ copying ? 'sync' : 'done_all' }}</span>
              {{ copying ? 'Menyalin...' : 'Konfirmasi Salin' }}
            </button>
          </footer>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
        <div class="delete-modal">
          <div class="delete-modal-icon">
            <span class="material-symbols-outlined icon-xl">warning</span>
          </div>
          <h3 class="delete-modal-title">Hapus Kelas?</h3>
          <p class="delete-modal-text">
            Data kelas yang dihapus bisa memengaruhi data lain (Siswa, Nilai, Absen). Data yang dihapus tidak dapat dikembalikan. Lanjutkan?
          </p>
          <div class="delete-modal-actions">
            <button class="btn btn-secondary" @click="closeDeleteModal">Batal</button>
            <button class="btn btn-danger" @click="confirmDelete">Ya, Hapus</button>
          </div>
        </div>
      </div>
      
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
import Toast from '@/Components/Toast.vue';
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';

const kelasList = ref([]);
const jurusans = ref([]);
const tahunAjarans = ref([]);
const gurus = ref([]);
const showModal = ref(false);
const showCopyModal = ref(false);
const showDeleteModal = ref(false);
const isEdit = ref(false);
const copying = ref(false);
const editId = ref(null);
const deleteId = ref(null);
const lembaga = ref(null);
const jenjang = ref('SMA / MA');
const sessionYearName = ref('');

const form = reactive({
    nama: '',
    ruangan: '',
    tingkat: '10',
    jurusan_id: '',
    tahun_ajaran_id: '',
    wali_kelas_id: null
});

const copyForm = reactive({
    source_year_id: '',
    target_year_id: ''
});

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

const fetchData = async () => {
    try {
        const response = await axios.get('/api/admin/classes');
        lembaga.value = response.data.lembaga || null;
        if (lembaga.value?.jenjang) {
            jenjang.value = lembaga.value.jenjang;
        }
        
        jurusans.value = response.data.jurusans || [];
        tahunAjarans.value = response.data.tahun_ajarans || [];
        
        gurus.value = response.data.gurus || [];

        kelasList.value = response.data.kelas || [];

        sessionYearName.value = response.data.session_academic_year || '';
        // Default selection
        if (!form.tahun_ajaran_id && tahunAjarans.value.length > 0) {
            const match = sessionYearName.value
              ? tahunAjarans.value.find(t => t.nama === sessionYearName.value)
              : (lembaga.value?.tahun_ajaran
                ? tahunAjarans.value.find(t => t.nama === lembaga.value.tahun_ajaran)
                : null);
            form.tahun_ajaran_id = match ? match.id : tahunAjarans.value[0].id;
        }
        if (!form.jurusan_id && jurusans.value.length > 0) {
            const umum = jurusans.value.find(j => j.nama === 'Umum');
            form.jurusan_id = umum ? umum.id : jurusans.value[0].id;
        }
    } catch (error) {
        console.error('Error fetching data:', error);
        showToast('error', 'Error', 'Gagal memuat data kelas.');
    }
};

const openModal = () => {
    isEdit.value = false;
    form.nama = '';
    form.ruangan = '';
    form.tingkat = tingkatOptions.value[0] || '10';
    if (jurusans.value.length > 0) {
        const umum = jurusans.value.find(j => j.nama === 'Umum');
        form.jurusan_id = umum ? umum.id : jurusans.value[0].id;
    }
    if (tahunAjarans.value.length > 0) {
        const match = sessionYearName.value
          ? tahunAjarans.value.find(t => t.nama === sessionYearName.value)
          : (lembaga.value?.tahun_ajaran
            ? tahunAjarans.value.find(t => t.nama === lembaga.value.tahun_ajaran)
            : null);
        form.tahun_ajaran_id = match ? match.id : tahunAjarans.value[0].id;
    }
    showModal.value = true;
};

const openCopyModal = () => {
    if (tahunAjarans.value.length < 2) {
        showToast('error', 'Perhatian', 'Minimal harus ada 2 Tahun Ajaran untuk menyalin kelas.');
        return;
    }
    copyForm.source_year_id = '';
    // Default target is current session year
    const match = sessionYearName.value
      ? tahunAjarans.value.find(t => t.nama === sessionYearName.value)
      : (lembaga.value?.tahun_ajaran
        ? tahunAjarans.value.find(t => t.nama === lembaga.value.tahun_ajaran)
        : null);
    copyForm.target_year_id = match ? match.id : tahunAjarans.value[0].id;
    showCopyModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const closeCopyModal = () => {
    showCopyModal.value = false;
};

const editKelas = (item) => {
    isEdit.value = true;
    editId.value = item.id;
    form.nama = item.nama;
    form.ruangan = item.ruangan;
    form.tingkat = item.tingkat;
    form.jurusan_id = item.jurusan_id;
    form.tahun_ajaran_id = item.tahun_ajaran_id;
    form.wali_kelas_id = item.wali_kelas_id;
    showModal.value = true;
};

const getWaliKelasName = (id) => {
    if (!id) return 'Belum ditentukan';
    const guru = gurus.value.find(g => g.id === id);
    return guru ? guru.nama : 'Guru tidak ditemukan';
};

const getWaliKelasNIP = (id) => {
    if (!id) return 'Silakan set wali kelas';
    const guru = gurus.value.find(g => g.id === id);
    return guru && guru.nip ? `NIP. ${guru.nip}` : 'NIP. -';
};

const getWaliKelasInitials = (id) => {
    if (!id) return '?';
    const guru = gurus.value.find(g => g.id === id);
    if (!guru) return '?';
    return guru.nama.substring(0, 2).toUpperCase();
};

const submitForm = async () => {
    try {
        if (isEdit.value) {
            await axios.put(`/api/admin/classes/${editId.value}`, form);
            showToast('success', 'Berhasil', 'Data kelas berhasil diperbarui.');
        } else {
            await axios.post('/api/admin/classes', form);
            showToast('success', 'Berhasil', 'Kelas baru berhasil ditambahkan.');
        }
        closeModal();
        fetchData();
    } catch (error) {
        console.error('Error submitting form:', error);
        const msg = error.response?.data?.message || 'Terjadi kesalahan.';
        showToast('error', 'Gagal', msg);
    }
};

const submitCopy = async () => {
    if (!copyForm.source_year_id || !copyForm.target_year_id) {
        showToast('error', 'Perhatian', 'Pilih tahun ajaran sumber dan target.');
        return;
    }

    copying.value = true;
    try {
        const res = await axios.post('/api/admin/classes/copy', copyForm);
        showToast('success', 'Berhasil', res.data.message);
        closeCopyModal();
        fetchData();
    } catch (error) {
        console.error('Error copying classes:', error);
        const msg = error.response?.data?.message || 'Terjadi kesalahan saat menyalin kelas.';
        showToast('error', 'Gagal', msg);
    } finally {
        copying.value = false;
    }
};

const deleteKelas = (id) => {
    deleteId.value = id;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteId.value = null;
};

const confirmDelete = async () => {
    try {
        await axios.delete(`/api/admin/classes/${deleteId.value}`);
        showToast('success', 'Terhapus', 'Kelas berhasil dihapus.');
        fetchData();
        closeDeleteModal();
    } catch (error) {
        showToast('error', 'Gagal', 'Gagal menghapus kelas.');
    }
};

onMounted(() => {
    fetchData();
});

const tingkatOptions = computed(() => {
    const val = (jenjang.value || '').toUpperCase();
    if (val.includes('SD') || val.includes('MI')) {
        return ['1', '2', '3', '4', '5', '6'];
    }
    if (val.includes('SMP') || val.includes('MTS')) {
        return ['7', '8', '9'];
    }
    return ['10', '11', '12'];
});

const filteredKelas = computed(() => kelasList.value);

const displayTahunAjaran = computed(() => {
    if (form.tahun_ajaran_id) {
        const ta = tahunAjarans.value.find(t => t.id === form.tahun_ajaran_id);
        if (ta?.nama) return ta.nama;
    }
    if (sessionYearName.value) return sessionYearName.value;
    return lembaga.value?.tahun_ajaran || '-';
});
</script>

<style scoped>
/* Page Layout */
.page-container {
    padding: 2rem 2.5rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Header */
.page-header {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.header-blue {
    background: var(--color-primary, #1e40af);
    color: #ffffff;
    padding: 1.5rem 1.75rem;
    border-radius: 1.5rem;
    box-shadow: 0 12px 24px -18px rgba(30, 64, 175, 0.6);
}

@media (min-width: 768px) {
    .page-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
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

/* Header Actions */
.header-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1rem;
}

/* Button UI */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    font-family: inherit;
}

.btn-primary {
    background-color: #ffffff;
    color: var(--color-primary, #1e40af);
}

.btn-primary:hover {
    background-color: #e2e8f0;
}

.btn-outline {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
}

.btn-outline:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.btn-add {
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 700;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

/* Table Card */
.table-card {
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 10px 24px -14px rgba(15, 23, 42, 0.35);
    border: 1px solid var(--border-color, #e2e8f0);
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
    background-color: #1e40af;
    padding: 0.9rem 1.25rem;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #ffffff;
    border-bottom: 2px solid #1e3a8a;
}

.data-table td {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 0.8rem;
}

.hover-row:hover td {
    background-color: #f8fafc;
}

.data-table tbody tr:nth-child(even) td {
    background-color: #fcfdff;
}

/* Table Cells */
.flex-center {
    display: flex;
    align-items: center;
}

.gap-sm { gap: 0.625rem; }
.gap-md { gap: 1rem; }

.icon-box {
    width: 3rem;
    height: 3rem;
    background-color: #eff6ff;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary, #3b82f6);
}

.circle-icon {
    width: 2rem;
    height: 2rem;
    background-color: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-lg { font-size: 1.5rem; font-weight: 700; }
.icon-sm { font-size: 1.125rem; color: #64748b; }

.item-title {
    font-size: 1rem;
    font-weight: 900;
    color: var(--color-text-main, #0f172a);
    line-height: 1.2;
}

.item-subtitle {
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 0.125rem;
}

.item-text {
    font-size: 0.875rem;
    font-weight: 700;
    color: #334155;
}

.avatar-circle {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 50%;
    background-color: #f1f5f9;
    border: 2px solid white;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    color: #94a3b8;
    font-size: 1.125rem;
}

.wk-name {
    font-size: 0.875rem;
    font-weight: 800;
    color: var(--color-text-main, #0f172a);
}

.wk-nip {
    font-size: 0.75rem;
    font-weight: 600;
    color: #94a3b8;
}

/* Actions */
.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    opacity: 0.6;
    transition: opacity 0.2s;
}

.hover-row:hover .action-buttons {
    opacity: 1;
}

.btn-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f1f5f9;
    color: #64748b;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-edit:hover {
    background-color: #f59e0b;
    color: white;
}

.btn-delete:hover {
    background-color: #ef4444;
    color: white;
}

.text-right { text-align: right; }
.th-nama { padding-left: 2.5rem; }
.td-nama { padding-left: 2.5rem; }

.empty-state {
    text-align: center;
    padding: 3rem;
    font-weight: 700;
    color: #64748b;
}

.empty-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.empty-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 1rem;
    background: #eff6ff;
    color: var(--color-primary, #3b82f6);
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-title {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
}

.empty-desc {
    font-size: 0.8rem;
    color: #94a3b8;
    font-weight: 600;
}

/* Pagination */
.pagination-footer {
    padding: 1.5rem 2.5rem;
    background-color: #f8fafc;
    border-top: 1px solid var(--border-color, #e2e8f0);
}

.pagination-info {
    font-size: 0.875rem;
    font-weight: 700;
    color: #64748b;
}

.text-highlight {
    color: var(--color-text-main, #0f172a);
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.class-modal {
    background: #ffffff;
    width: 92%;
    max-width: 860px;
    border-radius: 2rem;
    box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;
    animation: bounceIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes bounceIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.class-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 2rem 2.5rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    background: #ffffff;
}

.class-modal-header.compact {
    padding: 1.25rem 2rem 1rem;
}

.class-modal-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.title-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 1rem;
    background: #4f46e5;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 12px 20px rgba(79, 70, 229, 0.25);
}

.class-modal-title h3 {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.2rem;
}

.class-modal-title p {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
}

.close-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 999px;
    border: none;
    background: transparent;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.close-icon:hover {
    background: #f1f5f9;
    color: #475569;
}

.class-modal-body {
    padding: 1.5rem 2rem 2rem;
    overflow-y: auto;
}

.year-hint {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    margin-bottom: 1.5rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.95rem;
    font-weight: 800;
    color: #0f172a;
    margin: 1.25rem 0 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.step-badge {
    width: 2rem;
    height: 2rem;
    border-radius: 0.75rem;
    background: #eef2ff;
    color: #4f46e5;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.85rem;
}

.step-badge.alt {
    background: #ecfdf3;
    color: #10b981;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.25rem;
}

.form-grid.single {
    grid-template-columns: 1fr;
}

.modal-split {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 1.5rem;
    align-items: start;
}

.split-col {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group.full {
    grid-column: span 2;
}

.helper-text {
    margin-top: 0.35rem;
    font-size: 0.7rem;
    color: #94a3b8;
    font-weight: 600;
}

.class-modal-footer {
    padding: 1.25rem 2rem;
    border-top: 1px solid #f1f5f9;
    background: #f8fafc;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

/* Delete Modal Styling */
.delete-modal {
    width: 92%;
    max-width: 420px;
    background: #ffffff;
    border-radius: 1.5rem;
    padding: 2rem 2.25rem;
    text-align: center;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: bounceIn 0.3s ease;
}

.delete-modal-icon {
    width: 4rem;
    height: 4rem;
    background-color: #fef2f2;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ef4444;
    margin-bottom: 1rem;
}

.icon-xl { font-size: 2rem; }

.delete-modal-title {
    font-size: 1.1rem;
    font-weight: 900;
    color: var(--color-text-main, #0f172a);
    margin-bottom: 0.5rem;
}

.delete-modal-text {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.delete-modal-actions {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
}

.btn-danger {
    background-color: #ef4444;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-danger:hover {
    background-color: #dc2626;
}

.btn-secondary {
    background-color: #f1f5f9;
    color: #64748b;
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    font-weight: 700;
}

.btn-secondary:hover {
    background-color: #e2e8f0;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.7rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    background-color: #f8fafc;
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 0.75rem;
    font-size: 0.8rem;
    color: var(--color-text-main, #334155);
    font-weight: 700;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: var(--color-primary, #3b82f6);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    background-color: white;
}

@media (max-width: 768px) {
    .header-blue {
        padding: 1.25rem 1.5rem;
    }

    .class-modal-header,
    .class-modal-body,
    .class-modal-footer {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    .modal-split {
        grid-template-columns: 1fr;
    }

    .form-group.full {
        grid-column: span 1;
    }
}
</style>
