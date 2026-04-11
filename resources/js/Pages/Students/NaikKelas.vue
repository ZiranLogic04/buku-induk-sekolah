<template>
  <div class="nk-shell">
    <div class="nk-content">
      <div class="nk-hero">
        <div>
          <h1 class="nk-title">Naik Kelas</h1>
          <p class="nk-subtitle">Kelola kenaikan tingkat siswa dan kelulusan secara massal.</p>
        </div>
        <div class="nk-hero-badge">
          Mode: {{ isGraduateOnly ? 'Lulus' : 'Naik Kelas' }}
        </div>
      </div>

      <div class="nk-config">
        <div class="nk-config-col">
          <div class="nk-config-title">
            <span class="nk-icon-circle">
              <span class="material-symbols-outlined">logout</span>
            </span>
            <h2>Konfigurasi Kelas Asal</h2>
          </div>
          <div class="nk-config-grid">
            <div>
              <label class="nk-label">Tahun Ajaran Asal</label>
              <select class="nk-input" v-model="activeYearId" @change="handleYearChange">
                <option v-for="ta in availableYears" :key="ta.id" :value="ta.id">{{ ta.nama }}</option>
              </select>
            </div>
            <div>
              <label class="nk-label">Pilih Kelas Asal</label>
              <select class="nk-input" v-model="sourceClass" @change="fetchSourceStudents">
                <option value="">Pilih Kelas...</option>
                <option v-for="cls in sourceClasses" :key="cls.id" :value="cls.id">{{ cls.nama }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="nk-config-col">
          <div class="nk-config-title">
            <span class="nk-icon-circle alt">
              <span class="material-symbols-outlined">login</span>
            </span>
            <h2>Konfigurasi Kelas Tujuan</h2>
          </div>
          <div class="nk-config-grid">
            <div>
              <label class="nk-label">Tahun Ajaran Tujuan</label>
              <input class="nk-input" type="text" :value="targetYearName" readonly />
            </div>
            <div>
              <label class="nk-label">Target Kelas Tujuan</label>
              <input class="nk-input" type="text" :value="targetClassName" readonly />
            </div>
          </div>
          <div class="nk-config-hint" v-if="isGraduateOnly">
            Mode Lulus aktif karena kelas asal sudah mentok.
          </div>
          <div class="nk-config-hint warning" v-else-if="!targetClassName || targetClassName === '-'">
            Kelas tujuan belum tersedia di tahun ajaran berikutnya.
          </div>
        </div>
      </div>

      <div class="nk-zone-grid">
        <section class="nk-zone">
          <div class="nk-zone-header">
            <div class="nk-zone-title">
              <span>Siswa Kelas Asal</span>
              <span class="nk-chip">{{ sourceStudents.length }} Siswa</span>
            </div>
            <div class="nk-zone-actions">
              <label class="nk-check-all">
                <span>Pilih Semua</span>
                <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" :disabled="sourceStudents.length === 0" />
              </label>
            </div>
          </div>
          <div class="nk-zone-body">
            <table class="nk-table">
              <thead>
                <tr>
                  <th class="w-10">
                    <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" :disabled="sourceStudents.length === 0" />
                  </th>
                  <th>NIS</th>
                  <th>NISN</th>
                  <th>Nama Siswa</th>
                  <th>TTL</th>
                  <th>Umur</th>
                  <th>JK</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="student in filteredSourceStudents" :key="student.id" :class="{ selected: selectedStudents.includes(student.id) }">
                  <td>
                    <input type="checkbox" :value="student.id" v-model="selectedStudents" @change="setDefaultStatus(student.id)" />
                  </td>
                  <td class="muted">{{ student.nis || '-' }}</td>
                  <td class="muted">{{ student.nisn || '-' }}</td>
                  <td class="bold">{{ student.nama }}</td>
                  <td class="muted">{{ formatTtl(student) }}</td>
                  <td class="muted">{{ getAge(student.tanggal_lahir) }}</td>
                  <td class="muted">{{ student.jenis_kelamin }}</td>
                </tr>
                <tr v-if="filteredSourceStudents.length === 0">
                  <td colspan="7" class="nk-empty">Pilih kelas asal terlebih dahulu.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="nk-zone target">
          <div class="nk-zone-header">
            <div class="nk-zone-title">
              <span>Preview Kelas Tujuan</span>
              <span class="nk-chip alt">Selected: {{ selectedStudents.length }}</span>
            </div>
          </div>
          <div class="nk-zone-body">
            <table class="nk-table">
              <thead>
                <tr>
                  <th>NIS</th>
                  <th>NISN</th>
                  <th>Nama Siswa</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="selectedStudents.length === 0">
                  <td colspan="3" class="nk-empty">Staging area kosong.</td>
                </tr>
                <tr v-for="student in stagedStudents" :key="student.id">
                  <td class="muted">{{ student.nis || '-' }}</td>
                  <td class="muted">{{ student.nisn || '-' }}</td>
                  <td class="bold">{{ student.nama }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <div class="nk-action">
        <button class="nk-button" @click="processMove" :disabled="selectedStudents.length === 0 || (!isGraduateOnly && !targetClass)">
          {{ isGraduateOnly ? 'Proses Kelulusan' : 'Proses Naik Kelas' }}
        </button>
        <p class="nk-action-note">Pastikan data siswa sudah sesuai sebelum memproses.</p>
      </div>
    </div>
    <Toast :show="toast.show" :type="toast.type" :title="toast.title" :message="toast.message" @close="toast.show = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import axios from 'axios';
import Toast from '@/Components/Toast.vue';

const classes = ref([]);
const availableYears = ref([]); // Tahun Ajaran
const sourceClass = ref('');
const targetClass = ref('');
const activeYearId = ref('');
const activeYearName = ref('-');
const targetYearId = ref('');
const targetYearName = ref('-');
const isGraduateOnly = ref(false);
const jenjang = ref('');

const searchSource = ref('');

const sourceStudents = ref([]);
const selectedStudents = ref([]);

const toast = reactive({ show: false, type: 'success', title: '', message: '' });
const showToast = (type, title, message) => { toast.type = type; toast.title = title; toast.message = message; toast.show = true; };

const filteredSourceStudents = computed(() => {
    if (!searchSource.value) return sourceStudents.value;
    const lowerSearch = searchSource.value.toLowerCase();
    return sourceStudents.value.filter(s => s.nama.toLowerCase().includes(lowerSearch) || s.nis?.includes(lowerSearch));
});

const isAllSelected = computed(() => {
    return filteredSourceStudents.value.length > 0 && selectedStudents.value.length === filteredSourceStudents.value.length;
});

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedStudents.value = filteredSourceStudents.value.map(s => s.id);
        selectedStudents.value.forEach(id => setDefaultStatus(id));
    } else {
        selectedStudents.value = [];
    }
};

const setDefaultStatus = () => {};

const formatDateShort = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatTtl = (student) => {
    const tempat = student?.tempat_lahir || '-';
    const tanggal = student?.tanggal_lahir ? formatDateShort(student.tanggal_lahir) : '-';
    if (tempat === '-' && tanggal === '-') return '-';
    return `${tempat}, ${tanggal}`;
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

const stagedStudents = computed(() => {
    const map = new Map(sourceStudents.value.map(s => [s.id, s]));
    return selectedStudents.value.map(id => map.get(id)).filter(Boolean);
});

const sourceClasses = computed(() => {
    if (!activeYearId.value) return [];
    return classes.value.filter(c => c.tahun_ajaran_id === activeYearId.value);
});

const targetClassName = computed(() => {
    if (isGraduateOnly.value) return 'Lulus';
    const target = classes.value.find(c => c.id === targetClass.value);
    return target ? target.nama : '-';
});

const getMaxTingkat = () => {
    const val = (jenjang.value || '').toUpperCase();
    if (val.includes('SD') || val.includes('MI')) return 6;
    if (val.includes('SMP') || val.includes('MTS')) return 9;
    if (val.includes('SMA') || val.includes('MA') || val.includes('SMK')) return 12;
    return 12;
};

const computeTargetClass = () => {
    if (!sourceClass.value) {
        targetClass.value = '';
        isGraduateOnly.value = false;
        return;
    }
    if (!targetYearId.value) {
        targetClass.value = '';
        isGraduateOnly.value = false;
        return;
    }
    const cls = classes.value.find(c => c.id === sourceClass.value);
    if (!cls) {
        targetClass.value = '';
        isGraduateOnly.value = false;
        return;
    }

    const tingkatMatch = String(cls.tingkat || '').match(/\d+/);
    const tingkat = tingkatMatch ? parseInt(tingkatMatch[0], 10) : null;
    if (!tingkat) {
        targetClass.value = '';
        isGraduateOnly.value = false;
        return;
    }

    const maxTingkat = getMaxTingkat();
    const nextTingkat = tingkat + 1;
    if (tingkat >= maxTingkat) {
        targetClass.value = '';
        isGraduateOnly.value = true;
        return;
    }
    const target = classes.value.find(c =>
        c.tahun_ajaran_id === targetYearId.value &&
        String(c.tingkat || '').includes(String(nextTingkat)) &&
        (cls.jurusan_id ? c.jurusan_id === cls.jurusan_id : true)
    );

    if (!target) {
        targetClass.value = '';
        isGraduateOnly.value = false;
        return;
    }

    isGraduateOnly.value = false;
    targetClass.value = target.id;
};

const handleYearChange = () => {
    const activeYear = availableYears.value.find(y => y.id === activeYearId.value);
    if (activeYear) {
        activeYearName.value = activeYear.nama;
    }
    const activeIndex = availableYears.value.findIndex(y => y.id === activeYearId.value);
    const nextYear = activeIndex > 0 ? availableYears.value[activeIndex - 1] : null;
    if (nextYear) {
        targetYearId.value = nextYear.id;
        targetYearName.value = nextYear.nama;
    } else {
        targetYearId.value = '';
        targetYearName.value = '-';
    }
    sourceClass.value = '';
    sourceStudents.value = [];
    selectedStudents.value = [];
};

const fetchClasses = async () => {
    try {
        const response = await axios.get('/api/admin/classes'); 
        classes.value = response.data.kelas || [];
        availableYears.value = response.data.tahun_ajarans || [];
        jenjang.value = response.data.lembaga?.jenjang || '';
        
        const sessionYear = response.data.session_academic_year || response.data.lembaga?.tahun_ajaran;
        const activeYear = availableYears.value.find(y => y.nama === sessionYear) || availableYears.value[0];
        
        if (activeYear) {
            activeYearId.value = activeYear.id;
            activeYearName.value = activeYear.nama;
            handleYearChange();
        }
    } catch (e) { console.error(e); }
};

const fetchSourceStudents = async () => {
    selectedStudents.value = [];
    if (!sourceClass.value || !activeYearId.value) { sourceStudents.value = []; return; }
    try {
        const response = await axios.get('/api/admin/students', { params: { kelas_id: sourceClass.value, nopaginate: true } });
        sourceStudents.value = response.data.students.data || response.data.students || [];
        computeTargetClass();
    } catch (e) { showToast('error', 'Error', 'Gagal memuat siswa asal'); }
};


const processMove = async () => {
    if (selectedStudents.value.length === 0) return showToast('error', 'Peringatan', 'Pilih minimal 1 siswa!');
    
    if (!isGraduateOnly.value) {
        if (!targetClass.value) return showToast('error', 'Peringatan', 'Kelas tujuan belum tersedia!');
        if (sourceClass.value === targetClass.value) return showToast('error', 'Peringatan', 'Kelas Asal dan Tujuan tidak boleh sama!');
    }

    try {
        // Collect targeted actions based on per-row dropdown status
        const actionData = selectedStudents.value.map(id => ({
            student_id: id,
            action: isGraduateOnly.value ? 'Lulus' : 'Naik'
        }));

        await axios.post('/api/admin/students/bulk-move', {
            target_kelas_id: isGraduateOnly.value ? null : targetClass.value,
            target_type: isGraduateOnly.value ? 'lulus' : 'kelas',
            actions: actionData
        });
        showToast('success', 'Berhasil', `${selectedStudents.value.length} Siswa berhasil di-eksekusi.`);
        selectedStudents.value = [];
        fetchSourceStudents();
        if (!isGraduateOnly.value) {
            // No preview fetch needed for target class; right table is staging area.
        }
    } catch (error) {
        showToast('error', 'Gagal', 'Terjadi kesalahan saat mengeksekusi data siswa.');
    }
};

onMounted(() => {
    fetchClasses();
});
</script>

<style scoped>
/* Shell */
.nk-shell {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: #f8fafc;
    min-height: 100vh;
    color: #0f172a;
}
.nk-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem 1.5rem 3rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
@media (min-width: 768px) {
    .nk-content { padding: 2rem 2rem 3rem; }
}

/* Hero */
.nk-hero {
    background: #1e40af;
    color: #ffffff;
    padding: 1.5rem 2rem;
    border-radius: 1.5rem;
    box-shadow: 0 20px 40px -24px rgba(30, 64, 175, 0.6);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.nk-title {
    font-size: 1.9rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    margin-bottom: 0.3rem;
}
.nk-subtitle { font-size: 0.9rem; color: rgba(255, 255, 255, 0.8); }
.nk-hero-badge {
    padding: 0.4rem 1rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.3);
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

/* Config */
.nk-config {
    background: #ffffff;
    border-radius: 1.5rem;
    border: 1px solid #e2e8f0;
    padding: 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}
.nk-config-col { display: flex; flex-direction: column; gap: 1rem; }
.nk-config-title { display: flex; align-items: center; gap: 0.75rem; }
.nk-config-title h2 { font-size: 0.9rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #334155; }
.nk-icon-circle {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.9rem;
    background: #e0e7ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
}
.nk-icon-circle.alt { background: #ecfdf3; color: #10b981; }
.nk-config-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
.nk-label {
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    margin-bottom: 0.4rem;
    display: block;
}
.nk-input {
    width: 100%;
    padding: 0.7rem 0.9rem;
    border-radius: 0.9rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    font-size: 0.85rem;
    font-weight: 600;
    color: #0f172a;
}
.nk-config-hint {
    padding: 0.75rem 1rem;
    border-radius: 0.9rem;
    background: #ecfdf3;
    color: #065f46;
    font-size: 0.75rem;
    font-weight: 700;
    border: 1px solid #d1fae5;
}
.nk-config-hint.warning {
    background: #fff7ed;
    color: #c2410c;
    border-color: #fed7aa;
}

/* Zone grid */
.nk-zone-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
}
.nk-zone {
    background: #ffffff;
    border-radius: 1.5rem;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 480px;
}
.nk-zone.target { border-style: dashed; background: #f8fafc; }
.nk-zone-header {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.nk-zone-title { display: flex; align-items: center; gap: 0.75rem; font-weight: 800; color: #1e293b; }
.nk-chip {
    background: rgba(30, 64, 175, 0.12);
    color: #1e40af;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 800;
}
.nk-chip.alt { background: #1e40af; color: #ffffff; }
.nk-zone-actions { display: flex; align-items: center; gap: 0.6rem; }
.nk-check-all {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.7rem;
    font-weight: 700;
    color: #64748b;
}
.nk-check-all input { width: 1rem; height: 1rem; }
.nk-zone-body { flex: 1; overflow: auto; }

.nk-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}
.nk-table th {
    background: #1e40af;
    color: #ffffff;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 800;
    padding: 0.85rem 1rem;
    position: sticky;
    top: 0;
}
.nk-table td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.85rem;
}
.nk-table tbody tr.selected { background: #eef2ff; }
.nk-badge {
    display: inline-flex;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    background: #e0e7ff;
    color: #4f46e5;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
}
.nk-empty {
    text-align: center;
    color: #94a3b8;
    font-size: 0.8rem;
    padding: 2rem;
}
.muted { color: #64748b; font-size: 0.8rem; }
.bold { font-weight: 700; }

/* Action */
.nk-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}
.nk-button {
    padding: 0.85rem 2.5rem;
    border-radius: 1.1rem;
    background: #1e40af;
    color: #ffffff;
    font-weight: 900;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    border: none;
    cursor: pointer;
    box-shadow: 0 16px 30px -18px rgba(30, 64, 175, 0.7);
}
.nk-button:disabled { opacity: 0.5; cursor: not-allowed; }
.nk-action-note { font-size: 0.7rem; color: #94a3b8; font-weight: 600; }

@media (max-width: 768px) {
    .nk-hero { flex-direction: column; align-items: flex-start; }
}
</style>
