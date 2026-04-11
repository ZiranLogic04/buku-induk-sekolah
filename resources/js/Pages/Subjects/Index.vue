<template>
  <div class="subjects-page">
    <!-- Header -->
    <div class="page-header header-blue">
      <div class="header-content">
        <h1 class="page-title">Mata Pelajaran</h1>
        <p class="page-subtitle">Kelola daftar mata pelajaran, kelompok, tingkat, dan jurusan.</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary header-btn" @click="openModal()">
          <span class="material-symbols-outlined icon">add</span>
          Tambah Pelajaran
        </button>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-primary-light">
                <span class="material-symbols-outlined">menu_book</span>
            </div>
            <div class="stat-info">
                <p class="stat-label">Total Pelajaran</p>
                <p class="stat-value">{{ subjects.length }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-emerald">
                <span class="material-symbols-outlined">add_circle</span>
            </div>
            <div class="stat-info">
                <p class="stat-label">Baru Bulan Ini</p>
                 <p class="stat-value">{{ subjects.filter(s => new Date(s.created_at) > new Date(new Date().setDate(new Date().getDate() - 30))).length }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-amber">
                <span class="material-symbols-outlined">category</span>
            </div>
            <div class="stat-info">
                <p class="stat-label">Kelompok A Umum</p>
                <p class="stat-value">{{ countByGroup('A Umum') }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-purple">
                <span class="material-symbols-outlined">star</span>
            </div>
            <div class="stat-info">
                <p class="stat-label">Kelompok B Umum</p>
                <p class="stat-value">{{ countByGroup('B Umum') }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="table-filters">
        <div class="filter-item search">
            <span class="material-symbols-outlined">search</span>
            <input v-model="searchQuery" type="text" placeholder="Cari nama pelajaran..." />
        </div>
        <div class="filter-item">
            <span class="material-symbols-outlined">layers</span>
            <select v-model="filterLevel">
                <option value="">Semua Tingkat</option>
                <option v-for="level in tingkatLevels" :key="level" :value="level">Kelas {{ level }}</option>
            </select>
        </div>
        <div class="filter-item">
            <span class="material-symbols-outlined">explore</span>
            <select v-model="filterMajor">
                <option value="">Semua Jurusan</option>
                <option v-for="jurusan in jurusans" :key="jurusan" :value="jurusan">{{ jurusan }}</option>
            </select>
        </div>
        <div class="filter-meta">
            Menampilkan <strong>{{ filteredSubjects.length }}</strong> data
        </div>
    </div>

    <!-- Table Card -->
    <div class="card table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                         <th class="th-short">Skt</th>
                         <th class="th-name">Nama Pelajaran</th>
                         <th class="th-group">Kelompok</th>
                         <th class="th-level">Tingkat</th>
                         <th class="th-major">Jurusan</th>
                         <th class="th-order text-center">Urutan</th>
                         <th class="th-action text-right pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody v-if="loading">
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <p class="text-slate-400 mt-2">Memuat data...</p>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else-if="filteredSubjects.length === 0">
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <span class="material-symbols-outlined text-4xl text-slate-200">search_off</span>
                            <p class="text-slate-400 mt-2">Tidak ada data mata pelajaran.</p>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="item in filteredSubjects" :key="item.id" class="hover-row">
                        <td class="font-bold text-slate-600">{{ item.singkatan || '-' }}</td>
                        <td><span class="font-bold text-main">{{ item.nama }}</span></td>
                        <td><span class="text-xs font-medium text-slate-600">{{ item.kelompok || '-' }}</span></td>
                        <td>
                            <div class="badge-group">
                                <span v-for="t in normalizeTingkat(item.tingkat)" :key="t" class="badge badge-blue">
                                    {{ formatTingkatLabel(t) }}
                                </span>
                                <span v-if="normalizeTingkat(item.tingkat).length === 0">-</span>
                            </div>
                        </td>
                        <td>
                            <div class="badge-group">
                                <span class="badge badge-emerald">{{ item.jurusan || 'SEMUA' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="order-badge">{{ item.urutan ?? '-' }}</span>
                        </td>
                        <td class="text-right pr-6">
                             <div class="action-buttons">
                                <button class="btn-icon text-primary" @click="openModal(item)" title="Edit"><span class="material-symbols-outlined">edit_square</span></button>
                                <button class="btn-icon text-red" @click="deleteSubject(item)" title="Delete"><span class="material-symbols-outlined">delete</span></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Summary -->
        <div class="pagination-footer">
            <p class="showing-text">Menampilkan {{ filteredSubjects.length }} dari {{ subjects.length }} Mata Pelajaran</p>
        </div>
    </div>
    <!-- Form Modal -->
    <div v-if="showModal" class="modal-backdrop">
      <div class="modal-shell">
        <header class="modal-header">
          <div class="modal-title-wrap">
            <div class="modal-icon">
              <span class="material-symbols-outlined">book_4</span>
            </div>
            <div>
              <h3 class="modal-title">{{ editingId ? 'Edit Mata Pelajaran' : 'Master Data Mata Pelajaran' }}</h3>
              <p class="modal-subtitle">Input data lengkap pendaftaran mata pelajaran baru</p>
            </div>
          </div>
          <button class="modal-close" @click="closeModal" aria-label="Tutup">
            <span class="material-symbols-outlined">close</span>
          </button>
        </header>

        <form @submit.prevent="saveSubject" class="modal-body">
          <div class="section">
            <div class="form-section-title">
              <span class="step-badge">1</span>
              Informasi Identitas
            </div>

            <div class="grid-3">
              <div class="field span-2">
                <label><span class="material-symbols-outlined c-indigo">menu_book</span> Nama Mata Pelajaran</label>
                <input v-model="form.nama" type="text" placeholder="Contoh: Matematika Wajib" required />
              </div>
              <div class="field">
                <label><span class="material-symbols-outlined c-orange">dictionary</span> Singkatan</label>
                <input v-model="form.singkatan" type="text" placeholder="Contoh: MTK" />
              </div>
            </div>

            <div class="grid-2">
              <div class="field">
                <label><span class="material-symbols-outlined c-purple">category</span> Kelompok</label>
                <select v-model="form.kelompok">
                  <option value="">Pilih Kelompok</option>
                  <option value="A Umum">Kelompok A (Umum)</option>
                  <option value="B Umum">Kelompok B (Umum)</option>
                  <option value="C Umum">Kelompok C (Peminatan)</option>
                  <option value="A">A</option>
                  <option value="B">B</option>
                </select>
              </div>
              <div class="field">
                <label><span class="material-symbols-outlined c-emerald">school</span> Jurusan</label>
                <select v-model="form.jurusan">
                  <option value="">Pilih Jurusan</option>
                  <option value="SEMUA">Semua Jurusan</option>
                  <option v-for="j in jurusans" :key="j" :value="j">{{ j }}</option>
                </select>
              </div>
            </div>

            <div class="field">
              <label><span class="material-symbols-outlined c-pink">format_list_numbered</span> Urutan di Buku Induk</label>
              <input v-model="form.urutan" type="number" placeholder="Contoh: 1" />
            </div>
          </div>

          <div class="section">
            <div class="form-section-title">
              <span class="step-badge alt">2</span>
              Pengaturan Tingkat & Kelas
            </div>

            <div class="field">
              <label><span class="material-symbols-outlined c-indigo">checklist_rtl</span> Tingkat/Kelas (Multi-select)</label>
              <div class="checkbox-grid">
                <label v-for="level in tingkatLevels" :key="level" class="checkbox-chip">
                  <input type="checkbox" v-model="form.tingkat" :value="level" />
                  <span>Kelas {{ level }}</span>
                </label>
              </div>
            </div>
          </div>

          <footer class="modal-footer">
            <button type="button" class="btn-secondary" @click="closeModal">Batal</button>
            <button type="submit" class="btn-save" :disabled="saving">
              <span v-if="saving" class="material-symbols-outlined icon-spin">sync</span>
              <span v-else class="material-symbols-outlined">save</span>
              {{ editingId ? 'Simpan Perubahan' : 'Simpan Mata Pelajaran' }}
            </button>
          </footer>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-backdrop">
        <div class="modal-panel-premium max-w-md">
            <div class="modal-header-premium bg-red-600">
                <div class="header-left">
                    <div class="header-icon-box bg-white/20">
                        <span class="material-symbols-outlined icon-xl">warning</span>
                    </div>
                    <div class="header-text">
                        <h3 class="modal-heading-premium">Konfirmasi Hapus</h3>
                        <p class="modal-subheading-premium">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>
                <button class="close-btn" @click="closeDeleteModal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body-premium">
                <p class="text-slate-600 mb-6">
                    Apakah Anda yakin ingin menghapus mata pelajaran <span class="font-bold text-slate-800">"{{ itemToDelete?.nama }}"</span>?
                    Data yang dihapus tidak dapat dikembalikan.
                </p>
                <div class="form-footer-premium">
                    <button type="button" class="btn-secondary" @click="closeDeleteModal">Batal</button>
                    <button type="button" class="btn-save-premium bg-red-600 hover:bg-red-700" @click="confirmDelete" :disabled="deleting">
                        <span v-if="deleting" class="material-symbols-outlined icon-spin">sync</span>
                        <span v-else class="material-symbols-outlined">delete</span>
                        {{ deleting ? 'Menghapus...' : 'Hapus Permanen' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <Toast :show="toast.show" :type="toast.type" :title="toast.title" :message="toast.message" @close="toast.show = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import axios from 'axios';
import Toast from '@/Components/Toast.vue';

const subjects = ref([]);
const jurusans = ref([]);
const tingkatLevels = ref([]);
const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingId = ref(null);
const itemToDelete = ref(null);

const toast = reactive({ show: false, type: 'success', title: '', message: '' });

const showToast = (type, title, message) => {
    toast.type = type;
    toast.title = title;
    toast.message = message;
    toast.show = true;
};

const searchQuery = ref('');
const filterLevel = ref('');
const filterMajor = ref('');

const form = ref({
    nama: '',
    singkatan: '',
    kelompok: 'A Umum',
    tingkat: [],
    jurusan: 'SEMUA',
    urutan: 1
});

const errors = ref({});

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/admin/subjects');
        subjects.value = res.data.subjects;
        jurusans.value = res.data.jurusans;
        tingkatLevels.value = res.data.tingkatLevels;
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);

const normalizeTingkat = (value) => {
    if (!Array.isArray(value)) return [];
    return value.map((v) => String(v));
};

const normalizeJurusan = (value) => {
    if (!value) return '';
    return String(value).trim().toUpperCase();
};

const formatTingkatLabel = (value) => {
    const v = String(value);
    if (v === '10') return 'X';
    if (v === '11') return 'XI';
    if (v === '12') return 'XII';
    return v;
};

const matchesMajorFilter = (subjectJurusan, filterValue) => {
    const subj = normalizeJurusan(subjectJurusan);
    const filter = normalizeJurusan(filterValue);

    if (!filter) return true;
    if (!subj || subj === 'SEMUA') return true;
    return subj === filter;
};

const filteredSubjects = computed(() => {
    return subjects.value.filter((s) => {
        const matchesSearch = s.nama.toLowerCase().includes(searchQuery.value.toLowerCase());
        const tingkatList = normalizeTingkat(s.tingkat);
        const matchesLevel = !filterLevel.value || tingkatList.includes(String(filterLevel.value));
        const matchesMajor = matchesMajorFilter(s.jurusan, filterMajor.value);
        return matchesSearch && matchesLevel && matchesMajor;
    });
});

const countByGroup = (group) => {
    return subjects.value.filter(s => s.kelompok === group).length;
};

const openModal = (item = null) => {
    if (item) {
        editingId.value = item.id;
        form.value = { ...item, tingkat: item.tingkat || [] };
    } else {
        editingId.value = null;
        form.value = {
            nama: '',
            singkatan: '',
            kelompok: 'A Umum',
            tingkat: tingkatLevels.value.length > 0 ? tingkatLevels.value : ['10', '11', '12'],
            jurusan: 'SEMUA',
            urutan: subjects.value.length + 1
        };
    }
    errors.value = {}; // Reset errors
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const saveSubject = async () => {
    saving.value = true;
    try {
        if (editingId.value) {
            await axios.put(`/api/admin/subjects/${editingId.value}`, form.value);
            showToast('success', 'Berhasil', 'Mata pelajaran berhasil diperbarui.');
        } else {
            await axios.post('/api/admin/subjects', form.value);
            showToast('success', 'Berhasil', 'Mata pelajaran berhasil ditambahkan.');
        }
        await fetchData();
        closeModal();
    } catch (err) {
        console.error(err);
        if (err.response && err.response.status === 422) {
            errors.value = err.response.data.errors;
            const firstError = Object.values(errors.value)[0][0];
            showToast('error', 'Validasi Gagal', firstError);
        } else {
             showToast('error', 'Gagal', 'Gagal menyimpan data. Terjadi kesalahan sistem.');
        }
    } finally {
        saving.value = false;
    }
};

const deleteSubject = (item) => {
    itemToDelete.value = item;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    itemToDelete.value = null;
};

const confirmDelete = async () => {
    if (!itemToDelete.value) return;
    
    deleting.value = true;
    try {
        await axios.delete(`/api/admin/subjects/${itemToDelete.value.id}`);
        showToast('success', 'Terhapus', 'Mata pelajaran berhasil dihapus.');
        await fetchData();
        closeDeleteModal();
    } catch (err) {
        console.error(err);
        showToast('error', 'Gagal', 'Gagal menghapus data.');
    } finally {
        deleting.value = false;
    }
};
</script>

<style scoped>
.subjects-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .subjects-page {
        padding: 2rem;
    }
}

/* Header */
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
    justify-content: flex-start;
}

.header-btn {
    background-color: #ffffff;
    color: var(--color-primary, #1e40af);
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: 999px;
    padding: 0.6rem 1.1rem;
    font-size: 0.85rem;
    font-weight: 700;
    box-shadow: 0 6px 14px -8px rgba(15, 23, 42, 0.4);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.header-btn:hover {
    background-color: #e2e8f0;
    border-color: rgba(226, 232, 240, 0.9);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 640px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.stat-card {
    background-color: white;
    padding: 1.25rem;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon span { font-size: 1.5rem; }

.bg-primary-light { background-color: rgba(30, 64, 175, 0.1); color: var(--color-primary); }
.bg-emerald { background-color: rgba(16, 185, 129, 0.1); color: #059669; }
.bg-amber { background-color: rgba(245, 158, 11, 0.1); color: #d97706; }
.bg-purple { background-color: rgba(147, 51, 234, 0.1); color: #9333ea; }

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 0.625rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.1;
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

/* Table Card */
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

/* Cell Content */
.font-bold { font-weight: 700; }
.text-slate-600 { color: #475569; }
.text-main { color: #0f172a; }
.text-xs { font-size: 0.75rem; }
.text-center { text-align: center; }

.badge-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.badge {
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 1px solid transparent;
}

.badge-blue {
    background-color: #eff6ff;
    color: #2563eb;
    border-color: #dbeafe;
}

.badge-emerald {
    background-color: #ecfdf5;
    color: #059669;
    border-color: #d1fae5;
}

.order-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    background-color: #f1f5f9;
    color: #1e40af;
    border-radius: 0.75rem;
    font-weight: 800;
    font-size: 0.875rem;
    border: 1.5px solid #e2e8f0;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
}

.btn-icon {
    padding: 0.5rem;
    border-radius: 0.5rem;
    background: none;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-icon:hover {
    background-color: #f1f5f9;
}

.btn-icon.text-primary { color: var(--color-primary, #1e40af); }
.btn-icon.text-primary:hover { background-color: rgba(30, 64, 175, 0.1); }

.btn-icon.text-red { color: #ef4444; }
.btn-icon.text-red:hover { background-color: #fef2f2; }

/* Pagination */
.pagination-footer {
    padding: 1.5rem;
    background-color: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

@media (min-width: 768px) {
    .pagination-footer {
        flex-direction: row;
        justify-content: space-between;
    }
}

.showing-text {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
}

/* Modal Styles */
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

.modal-shell {
    background: #ffffff;
    width: 100%;
    max-width: 720px;
    border-radius: 2rem;
    box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;
    animation: slideUp 0.3s ease-out;
}

.modal-header {
    padding: 1.25rem 1.75rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}

.modal-title-wrap {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.modal-icon {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 0.9rem;
    background: #4f46e5;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
}

.modal-title {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}

.modal-subtitle {
    font-size: 0.75rem;
    color: #94a3b8;
    margin: 0.2rem 0 0;
}

.modal-close {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 999px;
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
}

.modal-close:hover {
    background: #f1f5f9;
}

.modal-body {
    padding: 1.5rem 1.75rem 1.75rem;
    overflow-y: auto;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9rem;
    font-weight: 800;
    color: #0f172a;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.step-badge {
    width: 1.6rem;
    height: 1.6rem;
    border-radius: 0.6rem;
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

.grid-3 {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 1rem;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.span-2 {
    grid-column: span 2;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.field label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.field input,
.field select {
    width: 100%;
    padding: 0.7rem 0.9rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #0f172a;
    outline: none;
}

.field input:focus,
.field select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
}

.checkbox-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.7rem;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    background: #f8fafc;
}

.checkbox-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.6rem;
    border-radius: 0.75rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    font-size: 0.75rem;
    font-weight: 700;
    color: #4f46e5;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f1f5f9;
}

.btn-save {
    padding: 0.7rem 1.4rem;
    background: #4f46e5;
    color: #ffffff;
    border: none;
    border-radius: 1rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 12px 20px -14px rgba(79, 70, 229, 0.5);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-save:hover {
    background: #4338ca;
}

.btn-secondary {
    padding: 0.7rem 1.4rem;
    border-radius: 1rem;
    font-weight: 700;
    color: #475569;
    background: white;
    border: 1px solid #e2e8f0;
}

.c-indigo { color: #4f46e5; }
.c-orange { color: #f59e0b; }
.c-purple { color: #7c3aed; }
.c-emerald { color: #10b981; }
.c-pink { color: #ec4899; }

/* Delete Modal Styling */
.modal-panel-premium {
    background-color: white;
    width: 100%;
    max-width: 420px;
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    animation: slideUp 0.3s ease-out;
}

.modal-header-premium.bg-red-600 {
    background-color: #ef4444;
}

.btn-save-premium.bg-red-600 {
    background-color: #ef4444;
}

.btn-save-premium.bg-red-600:hover {
    background-color: #dc2626;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.icon-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

@media (min-width: 768px) {
    .page-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}
</style>
