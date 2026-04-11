<template>
  <div class="assignments-page">
    <div class="page-header header-blue">
      <div class="header-content">
        <h1 class="page-title">Penugasan Mengajar</h1>
        <p class="page-subtitle">Atur pembagian jam mengajar guru berdasarkan kelas dan mata pelajaran.</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary header-btn" :disabled="!selectedClassId" @click="saveAssignments">
          <span class="material-symbols-outlined icon-sm">save</span>
          Simpan
        </button>
      </div>
    </div>

    <div class="table-filters">
      <div class="filter-item filter-class">
        <span class="material-symbols-outlined">class</span>
        <select v-model="selectedClassId" @change="onClassChange">
          <option disabled value="">Pilih Kelas</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">
            {{ cls.nama }}
          </option>
        </select>
      </div>
      <div class="filter-meta">
        Menampilkan <strong>{{ filteredSubjects.length }}</strong> data
      </div>
    </div>

    <div class="info-banner">
      <div class="info-left">
        <span class="material-symbols-outlined icon-primary">info</span>
        <p class="info-text">Daftar pelajaran otomatis berdasarkan tingkat dan jurusan kelas yang dipilih.</p>
      </div>
      <div class="info-right">
        <span class="status-dot"></span>
        <span class="status-text">Tersimpan Otomatis</span>
      </div>
    </div>

    <div class="card table-card">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th class="th-no">No.</th>
              <th class="th-subject">Nama Pelajaran</th>
              <th class="th-group">Kelompok</th>
              <th class="th-teacher">Guru Pengajar</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(subject, idx) in filteredSubjects" :key="subject.id">
              <td class="td-no">{{ String(idx + 1).padStart(2, '0') }}</td>
              <td>
                <p class="subject-name">{{ subject.nama }}</p>
                <p class="subject-code">{{ subject.singkatan || '-' }}</p>
              </td>
              <td>
                <span class="group-badge" :class="getGroupBadge(subject.kelompok)">{{ subject.kelompok || '-' }}</span>
              </td>
              <td class="td-teacher">
                <div class="teacher-select-wrapper" :class="{ empty: !draftAssignments[subject.id].guru_id }">
                  <div class="avatar-small bg-slate">
                    <span class="material-symbols-outlined icon-sm">person</span>
                  </div>
                  <select v-model="draftAssignments[subject.id].guru_id" class="teacher-select">
                    <option value="">— Pilih Guru Pengajar —</option>
                    <option v-for="g in teachers" :key="g.id" :value="g.id">{{ g.nama }}</option>
                  </select>
                </div>
              </td>
            </tr>
            <tr v-if="filteredSubjects.length === 0">
              <td colspan="5" class="empty-state">Pilih kelas untuk melihat daftar pelajaran.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="pagination-footer">
        <p class="showing-text">Menampilkan {{ filteredSubjects.length }} Data</p>
      </div>
    </div>

    <Toast :show="toast.show" :type="toast.type" :title="toast.title" :message="toast.message" @close="toast.show = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import Toast from '@/Components/Toast.vue';

const classes = ref([]);
const subjects = ref([]);
const teachers = ref([]);
const tahunAjarans = ref([]);
const jurusans = ref([]);

const selectedClassId = ref('');
const selectedYearId = ref('');

const assignments = ref([]);
const draftAssignments = reactive({});

const toast = reactive({
  show: false,
  type: 'success',
  title: '',
  message: ''
});

const selectedClass = computed(() => classes.value.find((c) => c.id === Number(selectedClassId.value)));

const mapJurusanName = (kelas) => {
  if (!kelas?.jurusan_id) return '';
  const j = jurusans.value.find((x) => x.id === kelas.jurusan_id);
  return j?.nama || '';
};

const subjectMatchesClass = (subject, kelas) => {
  if (!kelas) return false;
  const tingkat = String(kelas.tingkat || '').toUpperCase();
  const tingkatList = Array.isArray(subject.tingkat) ? subject.tingkat.map((t) => String(t).toUpperCase()) : [];
  const tingkatOk = tingkatList.length === 0 || tingkatList.includes(tingkat);

  const jurusanName = mapJurusanName(kelas).toUpperCase();
  const subjectJurusan = String(subject.jurusan || '').toUpperCase();
  const jurusanOk = !subjectJurusan || subjectJurusan === 'UMUM' || subjectJurusan === 'SEMUA' || subjectJurusan === jurusanName;

  return tingkatOk && jurusanOk;
};

const filteredSubjects = computed(() => {
  if (!selectedClass.value) return [];
  return subjects.value
    .filter((s) => subjectMatchesClass(s, selectedClass.value))
    .sort((a, b) => (a.urutan || 0) - (b.urutan || 0));
});

const getGroupBadge = (kelompok) => {
  if (!kelompok) return 'group-neutral';
  const k = kelompok.toLowerCase();
  if (k.includes('a')) return 'group-a';
  if (k.includes('b')) return 'group-b';
  if (k.includes('c')) return 'group-c';
  return 'group-neutral';
};

const hydrateDraft = () => {
  const map = {};
  assignments.value.forEach((a) => {
    map[a.subject_id] = {
      subject_id: a.subject_id,
      guru_id: a.guru_id || ''
    };
  });

  filteredSubjects.value.forEach((s) => {
    if (!map[s.id]) {
      map[s.id] = { subject_id: s.id, guru_id: '' };
    }
  });

  Object.keys(draftAssignments).forEach((k) => delete draftAssignments[k]);
  Object.entries(map).forEach(([k, v]) => {
    draftAssignments[k] = v;
  });
};

const fetchMeta = async () => {
  const [classesRes, subjectsRes, teachersRes, lembagaRes] = await Promise.all([
    axios.get('/api/admin/classes'),
    axios.get('/api/admin/subjects'),
    axios.get('/api/admin/teachers'),
    axios.get('/api/admin/lembaga')
  ]);

  classes.value = classesRes.data.kelas || [];
  jurusans.value = classesRes.data.jurusans || [];
  tahunAjarans.value = classesRes.data.tahun_ajarans || [];
  subjects.value = subjectsRes.data.subjects || [];
  teachers.value = teachersRes.data || [];

  const lembaga = lembagaRes.data?.lembaga;
  if (lembaga?.tahun_ajaran && tahunAjarans.value.length) {
    const match = tahunAjarans.value.find((t) => t.nama === lembaga.tahun_ajaran);
    if (match) selectedYearId.value = match.id;
  }
};

const loadAssignments = async () => {
  if (!selectedClassId.value) {
    assignments.value = [];
    hydrateDraft();
    return;
  }
  assignments.value = [];
  hydrateDraft();
  if (!selectedYearId.value) {
    toast.type = 'warning';
    toast.title = 'Tahun Ajaran';
    toast.message = 'Tahun ajaran aktif belum terdeteksi.';
    toast.show = true;
    return;
  }
  const params = {
    kelas_id: selectedClassId.value,
    tahun_ajaran_id: selectedYearId.value
  };
  const res = await axios.get('/api/admin/assignments', { params });
  assignments.value = res.data || [];
  hydrateDraft();
};

const onClassChange = async () => {
  await loadAssignments();
};

const saveAssignments = async () => {
  try {
    if (!selectedYearId.value) {
      toast.type = 'warning';
      toast.title = 'Tahun Ajaran';
      toast.message = 'Tahun ajaran aktif belum terdeteksi.';
      toast.show = true;
      return;
    }
    const payload = {
      kelas_id: selectedClassId.value,
      tahun_ajaran_id: selectedYearId.value,
      assignments: filteredSubjects.value.map((s) => ({
        subject_id: s.id,
        guru_id: draftAssignments[s.id]?.guru_id || null
      }))
    };
    await axios.post('/api/admin/assignments/bulk', payload);
    toast.type = 'success';
    toast.title = 'Berhasil';
    toast.message = 'Penugasan tersimpan.';
    toast.show = true;
    await loadAssignments();
  } catch (err) {
    toast.type = 'error';
    toast.title = 'Gagal';
    toast.message = err?.response?.data?.message || 'Gagal menyimpan penugasan.';
    toast.show = true;
  }
};

onMounted(async () => {
  await fetchMeta();
  await loadAssignments();
});
</script>

<style scoped>
.assignments-page {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .assignments-page { padding: 2.5rem; }
}

/* Header */
.page-header {
  display: flex;
  flex-direction: column;
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
.header-actions { display: flex; justify-content: flex-start; }
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
.header-btn:disabled { opacity: 0.6; cursor: not-allowed; }
@media (min-width: 768px) {
  .page-header { flex-direction: row; align-items: center; justify-content: space-between; }
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
.filter-class { flex: 1; min-width: 240px; }
.filter-item select {
  background: transparent;
  border: none;
  outline: none;
  font-size: 0.78rem;
  font-weight: 700;
  color: #0f172a;
  appearance: none;
  cursor: pointer;
  width: 100%;
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
  background-color: white;
  border-radius: 1.5rem;
  border: 1px solid var(--color-border);
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.info-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.9rem 1.1rem;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
}
.info-left { display: flex; align-items: center; gap: 0.75rem; }
.icon-primary { color: var(--color-primary); }
.info-text { font-size: 0.85rem; font-weight: 600; color: #64748b; }
.info-right { display: flex; align-items: center; gap: 0.5rem; }
.status-dot { width: 0.5rem; height: 0.5rem; background-color: #10b981; border-radius: 50%; }
.status-text {
  font-size: 0.6875rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.table-card {
  border-radius: 2.5rem;
  overflow: hidden;
}
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; }
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
.th-no { padding-left: 2.5rem; padding-right: 1.5rem; }
.th-teacher { padding-left: 1.5rem; padding-right: 2.5rem; }
.data-table td {
  padding: 1.5rem;
  border-bottom: 1px solid #f8fafc;
  vertical-align: middle;
}
.data-table tr:hover td { background-color: rgba(248, 250, 252, 0.5); }
.td-no {
  padding-left: 2.5rem;
  padding-right: 1.5rem;
  font-size: 0.875rem;
  font-weight: 900;
  color: #cbd5e1;
}
.subject-name { font-size: 1rem; font-weight: 700; color: var(--color-text-main); line-height: 1.25; }
.subject-code { font-size: 0.75rem; color: #94a3b8; font-weight: 500; margin-top: 0.125rem; }
.group-badge {
  display: inline-flex;
  padding: 0.25rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
}
.group-a { background-color: #eff6ff; color: #2563eb; }
.group-b { background-color: #fef3c7; color: #d97706; }
.group-c { background-color: #ecfdf5; color: #059669; }
.group-neutral { background-color: #e2e8f0; color: #475569; }

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

.td-teacher { padding-left: 1.5rem; padding-right: 2.5rem; }
.teacher-select-wrapper { position: relative; max-width: 24rem; }
.avatar-small {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.625rem;
  border: 2px solid white;
  z-index: 10;
}
.teacher-select {
  width: 100%;
  padding: 0.625rem 1rem 0.625rem 3rem;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
  outline: none;
  cursor: pointer;
  appearance: none;
  transition: all 0.2s;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 1rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
}
.teacher-select:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.2);
}
.teacher-select-wrapper.empty .avatar-small {
  background-color: #f1f5f9;
  color: #94a3b8;
}
.bg-slate { background-color: #f1f5f9; color: #94a3b8; }
.icon-sm { font-size: 0.875rem; }

.empty-state { text-align: center; padding: 2rem; color: #94a3b8; }
</style>
