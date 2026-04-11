<template>
  <div class="alumni-page">
    <div class="page-header header-blue">
      <div class="header-content">
        <h1 class="page-title">Data Alumni</h1>
        <p class="page-subtitle">Terbentuk otomatis saat status siswa berubah menjadi Lulus.</p>
      </div>
    </div>

    <div class="table-filters">
      <div class="filter-item search">
        <span class="material-symbols-outlined">search</span>
        <input v-model="searchQuery" type="text" placeholder="Cari nama / NIS / NISN..." />
      </div>
      <button class="filter-action" @click="fetchAlumni">
        <span class="material-symbols-outlined">filter_alt</span>
        Terapkan
      </button>
      <div class="filter-meta">
        Total <strong>{{ filteredAlumni.length }}</strong> data
      </div>
    </div>

    <div class="card table-card">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>NISN</th>
              <th>Nama Alumni</th>
              <th>Tahun Lulus</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in filteredAlumni" :key="item.id" class="hover-row">
              <td class="font-bold text-muted text-xs">{{ item.student?.nisn || '-' }}</td>
              <td class="font-bold text-slate-800">{{ item.student?.nama || '-' }}</td>
              <td class="text-sm text-slate-600">{{ item.tahun_lulus }}</td>
            </tr>
            <tr v-if="filteredAlumni.length === 0">
              <td colspan="3" class="empty-state">Belum ada data alumni.</td>
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

const alumni = ref([]);

const searchQuery = ref('');

const filteredAlumni = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return alumni.value;
  return alumni.value.filter((a) => {
    const nama = (a.student?.nama || '').toLowerCase();
    const nis = (a.student?.nis || '').toLowerCase();
    const nisn = (a.student?.nisn || '').toLowerCase();
    return nama.includes(q) || nis.includes(q) || nisn.includes(q);
  });
});

const fetchAlumni = async () => {
  const params = {};
  if (searchQuery.value) params.search = searchQuery.value;
  const res = await axios.get('/api/admin/alumni', { params });
  alumni.value = res.data || [];
};

onMounted(async () => {
  await fetchAlumni();
});
</script>

<style scoped>
.alumni-page {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
@media (min-width: 768px) { .alumni-page { padding: 2rem; } }

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
.text-slate-800 { color: #1e293b; }

.empty-state { text-align: center; padding: 2rem; color: #94a3b8; }
</style>
