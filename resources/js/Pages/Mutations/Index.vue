<template>
  <div class="mutations-page">
    <div class="page-header header-blue">
      <div class="header-content">
        <h1 class="page-title">Data Mutasi Siswa</h1>
        <p class="page-subtitle">Riwayat perubahan status siswa tercatat otomatis di sini.</p>
      </div>
    </div>

    <div class="table-filters">
      <div class="filter-item search">
        <span class="material-symbols-outlined">search</span>
        <input v-model="searchQuery" type="text" placeholder="Cari nama / NIS / NISN..." />
      </div>
      <div class="filter-item">
        <span class="material-symbols-outlined">category</span>
        <select v-model="filterJenis">
          <option value="">Semua Jenis</option>
          <option v-for="j in jenisOptions" :key="j" :value="j">{{ j }}</option>
        </select>
      </div>
      <button class="filter-action" @click="fetchMutations">
        <span class="material-symbols-outlined">filter_alt</span>
        Terapkan
      </button>
      <div class="filter-meta">
        Total <strong>{{ filteredMutations.length }}</strong> data
      </div>
    </div>

    <div class="card table-card">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>NIS / NISN</th>
              <th>Nama Siswa</th>
              <th>Jenis Mutasi</th>
              <th>Asal / Tujuan</th>
              <th>Kelas (Saat Mutasi)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in filteredMutations" :key="item.id" class="hover-row">
              <td class="text-sm text-slate-600">{{ formatDate(item.tanggal) }}</td>
              <td class="font-bold text-muted text-xs">
                {{ item.student?.nis || '-' }} / {{ item.student?.nisn || '-' }}
              </td>
              <td class="font-bold text-slate-800">{{ item.student?.nama || '-' }}</td>
              <td>
                <span class="badge" :class="getJenisBadge(item.jenis)">{{ item.jenis }}</span>
              </td>
              <td class="text-sm text-muted">{{ item.dari_ke || '-' }}</td>
              <td class="text-sm">{{ item.kelas?.nama || '-' }}</td>
            </tr>
            <tr v-if="filteredMutations.length === 0">
              <td colspan="6" class="empty-state">Belum ada data mutasi.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const mutations = ref([]);
const filterJenis = ref('');
const searchQuery = ref('');

const jenisOptions = [
  'Masuk',
  'Keluar'
];

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getJenisBadge = (jenis) => {
  if (jenis === 'Masuk') return 'badge-in';
  if (jenis === 'Keluar') return 'badge-out';
  return 'badge-neutral';
};

const filteredMutations = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return mutations.value;
  return mutations.value.filter((m) => {
    const nama = (m.student?.nama || '').toLowerCase();
    const nis = (m.student?.nis || '').toLowerCase();
    const nisn = (m.student?.nisn || '').toLowerCase();
    return nama.includes(q) || nis.includes(q) || nisn.includes(q);
  });
});

const fetchMutations = async () => {
  const params = {};
  if (filterJenis.value) params.jenis = filterJenis.value;
  const res = await axios.get('/api/admin/mutations', { params });
  mutations.value = res.data || [];
};

onMounted(async () => {
  await fetchMutations();
});
</script>

<style scoped>
.mutations-page {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
@media (min-width: 768px) { .mutations-page { padding: 2rem; } }

.page-header {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.header-blue {
  background: #1e40af;
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
.filter-action {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.85rem;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
}
.filter-action:hover { background: #f1f5f9; }
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

.text-sm { font-size: 0.875rem; }
.text-xs { font-size: 0.75rem; }
.text-muted { color: #64748b; }
.text-slate-600 { color: #475569; }
.text-slate-800 { color: #1e293b; }

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.625rem;
  font-weight: 900;
  text-transform: uppercase;
}
.badge-in { background-color: #d1fae5; color: #047857; }
.badge-out { background-color: #fee2e2; color: #b91c1c; }
.badge-neutral { background-color: #e2e8f0; color: #475569; }

.empty-state { text-align: center; padding: 2rem; color: #94a3b8; }
</style>
