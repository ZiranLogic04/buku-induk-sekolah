<template>
  <div class="attendance-shell" :class="{ 'bulk-panel-active': bulkDay }">
    <div class="attendance-container">
      <div class="attendance-hero">
        <div>
          <h1 class="hero-title">Absensi Siswa</h1>
          <p class="hero-subtitle">Input absensi harian per kelas.</p>
        </div>
        <div class="hero-pill">
          Tahun Ajaran: {{ selectedYear?.nama || '-' }}
        </div>
      </div>

      <div class="attendance-filters">
        <div class="filter-group">
          <label class="form-label-compact">Kelas</label>
          <select v-model="selectedClassId" class="form-input-compact" @change="loadStudents">
            <option value="">Pilih Kelas</option>
            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.nama }}</option>
          </select>
        </div>
        <div class="filter-group">
          <label class="form-label-compact">Bulan</label>
          <input v-model="selectedMonth" type="month" class="form-input-compact" @change="loadAttendance" />
        </div>
        <div class="filter-group">
          <label class="form-label-compact">Semester</label>
          <div class="filter-display">{{ selectedSemester }}</div>
        </div>
        <div class="filter-group actions">
          <button class="btn-outline" @click="loadAttendance">Tampilkan</button>
        </div>
      </div>

      <div class="attendance-card">
        <div class="attendance-card-header">
          <div class="attendance-card-title">
            <span>Daftar Absensi</span>
            <span class="attendance-badge">{{ saving ? 'Menyimpan...' : 'Siap disimpan' }}</span>
            <span class="attendance-hint">
              <span class="material-symbols-outlined">info</span>
              Klik tanggal untuk Bulk Edit
            </span>
          </div>
          <div class="flex gap-2">
            <button class="btn-outline" :disabled="saving || generating || !selectedClassId" @click="exportPdf">
              <span class="material-symbols-outlined">{{ generating ? 'sync' : 'picture_as_pdf' }}</span>
              {{ generating ? 'Memproses...' : 'Cetak PDF' }}
            </button>
            <button class="btn-primary" :disabled="saving || generating || !selectedClassId" @click="saveAttendance">
              <span class="material-symbols-outlined">save</span>
              Simpan Absensi
            </button>
          </div>
        </div>

        <div class="attendance-table-wrap">
          <table class="attendance-table">
            <thead>
              <tr>
                <th class="no-col">No</th>
                <th class="student-col">Siswa</th>
                <th
                  v-for="day in monthDays"
                  :key="day"
                  class="day-col"
                  :class="{ 'weekend-col': isWeekend(day) }"
                  @click="setBulkDay(day)"
                >
                  {{ dayLabel(day) }}
                  <span class="day-name">{{ dayName(day) }}</span>
                </th>
              </tr>
            </thead>
            <tbody v-if="students.length">
              <tr v-for="(s, idx) in students" :key="s.id" class="table-row-alt">
                <td class="no-cell">{{ idx + 1 }}</td>
                <td class="student-cell">
                  <div class="student-nis">{{ s.nis || '-' }}</div>
                  <div class="student-name">{{ s.nama }}</div>
                </td>
                <td
                  v-for="day in monthDays"
                  :key="day"
                  class="day-cell"
                  :class="{ 'weekend-col': isWeekend(day), 'selected-column': bulkDay === day }"
                >
                  <div class="cell-wrap">
                    <button
                      class="attendance-btn"
                      :class="statusClass(attendanceDraft[s.id][day].status)"
                      type="button"
                      @click="openCellPopover(s.id, day)"
                    >
                      {{ statusDisplay(attendanceDraft[s.id][day].status) }}
                    </button>
                    <div
                      v-if="isCellActive(s.id, day)"
                      class="cell-popover"
                    >
                      <button class="pop-btn status-h" @click="setStatus(s.id, day, 'H')">Hadir</button>
                      <button class="pop-btn status-i" @click="setStatus(s.id, day, 'I')">Izin</button>
                      <button class="pop-btn status-s" @click="setStatus(s.id, day, 'S')">Sakit</button>
                      <button class="pop-btn status-a" @click="setStatus(s.id, day, 'A')">Alfa</button>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td :colspan="monthDays.length + 1" class="empty-state">Pilih kelas untuk melihat siswa.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="legend">
          <div class="legend-item"><span class="dot dot-h"></span>H - Hadir</div>
          <div class="legend-item"><span class="dot dot-i"></span>I - Izin</div>
          <div class="legend-item"><span class="dot dot-s"></span>S - Sakit</div>
          <div class="legend-item"><span class="dot dot-a"></span>A - Alfa</div>
          <div class="legend-item"><span class="dot dot-l"></span>L - Libur</div>
        </div>
      </div>

    </div>

    <div v-if="bulkDay" class="bulk-backdrop" @click.self="clearBulkDay">
      <div id="bulk-edit-toolbar" class="bulk-toolbar">
        <div class="bulk-header">
          <div class="bulk-title">
            <span class="pulse-dot"></span>
            Bulk Edit: Tanggal {{ bulkDay ? bulkDay.slice(-2) : '--' }}
          </div>
          <button class="bulk-close" @click="clearBulkDay">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <p class="bulk-subtitle">Terapkan status berikut untuk semua siswa pada kolom yang dipilih:</p>
        <div class="bulk-grid">
        <button class="bulk-btn status-h" @click="applyBulkStatus('H')">HADIR</button>
        <button class="bulk-btn status-i" @click="applyBulkStatus('I')">IZIN</button>
        <button class="bulk-btn status-s" @click="applyBulkStatus('S')">SAKIT</button>
        <button class="bulk-btn status-a" @click="applyBulkStatus('A')">ALFA</button>
        <button class="bulk-btn status-l" @click="applyBulkStatus('L')">LIBUR</button>
      </div>
        <button class="bulk-cancel" @click="clearBulkDay">Batalkan Pilihan</button>
      </div>
    </div>

    <Toast :show="toast.show" :type="toast.type" :title="toast.title" :message="toast.message" @close="toast.show = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import Toast from '@/Components/Toast.vue';

const classes = ref([]);
const students = ref([]);
const tahunAjarans = ref([]);

const selectedClassId = ref('');
const selectedMonth = ref(new Date().toISOString().slice(0, 7));
const selectedSemester = ref('Ganjil');
const selectedYearId = ref('');
const bulkDay = ref('');

const attendanceDraft = reactive({});
const existingDates = ref(new Set());
const saving = ref(false);
const generating = ref(false);

const toast = reactive({ show: false, type: 'success', title: '', message: '' });

const selectedYear = computed(() => tahunAjarans.value.find((t) => t.id === Number(selectedYearId.value)));

const fetchMeta = async () => {
  const [classesRes, meRes, lembagaRes] = await Promise.all([
    axios.get('/api/admin/classes'),
    axios.get('/api/me'),
    axios.get('/api/admin/lembaga')
  ]);

  classes.value = classesRes.data.kelas || [];
  tahunAjarans.value = classesRes.data.tahun_ajarans || [];

  const ctx = meRes.data?.context;
  if (ctx?.semester) selectedSemester.value = ctx.semester;
  if (ctx?.academic_year && tahunAjarans.value.length) {
    const match = tahunAjarans.value.find((t) => t.nama === ctx.academic_year);
    if (match) selectedYearId.value = match.id;
  }

  if (!selectedYearId.value) {
    const lembaga = lembagaRes.data?.lembaga;
    if (lembaga?.semester && !selectedSemester.value) selectedSemester.value = lembaga.semester;
    if (lembaga?.tahun_ajaran && tahunAjarans.value.length) {
      const match = tahunAjarans.value.find((t) => t.nama === lembaga.tahun_ajaran);
      if (match) selectedYearId.value = match.id;
    }
  }
};

const loadStudents = async () => {
  if (!selectedClassId.value) {
    students.value = [];
    return;
  }
  const res = await axios.get('/api/admin/students', {
    params: { kelas_id: selectedClassId.value, nopaginate: true }
  });
  students.value = res.data.students || [];
  initDraft();
  await loadAttendance();
};

const initDraft = () => {
  Object.keys(attendanceDraft).forEach((k) => delete attendanceDraft[k]);
  students.value.forEach((s) => {
    attendanceDraft[s.id] = {};
    monthDays.value.forEach((day) => {
      attendanceDraft[s.id][day] = { status: '-', keterangan: '' };
    });
  });
};

const loadAttendance = async () => {
  if (!selectedClassId.value || !selectedMonth.value || !selectedYearId.value) return;
  if (students.value.length) {
    students.value.forEach((s) => {
      if (!attendanceDraft[s.id]) attendanceDraft[s.id] = {};
      monthDays.value.forEach((day) => {
        attendanceDraft[s.id][day] = { status: '-', keterangan: '' };
      });
    });
  }
  const res = await axios.get('/api/admin/attendances', {
    params: {
      kelas_id: selectedClassId.value,
      bulan: selectedMonth.value,
      tahun_ajaran_id: selectedYearId.value,
      semester: selectedSemester.value
    }
  });
  const data = res.data || [];
  existingDates.value = new Set();
  if (Array.isArray(data)) {
    data.forEach((attendance) => {
      if (!attendance?.tanggal || !attendance?.items) return;
      existingDates.value.add(attendance.tanggal);
      attendance.items.forEach((it) => {
        if (attendanceDraft[it.student_id] && attendanceDraft[it.student_id][attendance.tanggal]) {
          attendanceDraft[it.student_id][attendance.tanggal].status = it.status;
          attendanceDraft[it.student_id][attendance.tanggal].keterangan = it.keterangan || '';
        }
      });
    });
  }
};

const saveAttendance = async () => {
  if (!selectedClassId.value || !selectedYearId.value) return;
  saving.value = true;
  try {
    const payloads = monthDays.value.map((day) => {
      const items = students.value.map((s) => {
        const entry = attendanceDraft[s.id]?.[day];
        const rawStatus = entry?.status || '-';
        return {
          student_id: s.id,
          status: rawStatus === '-' ? null : rawStatus,
          keterangan: entry?.keterangan || null
        };
      });

      return {
        tanggal: day,
        kelas_id: selectedClassId.value,
        tahun_ajaran_id: selectedYearId.value,
        semester: selectedSemester.value,
        items
      };
    });

    await Promise.all(payloads.map((payload) => axios.post('/api/admin/attendances', payload)));
    toast.type = 'success';
    toast.title = 'Berhasil';
    toast.message = 'Absensi bulan ini tersimpan.';
    toast.show = true;
  } catch (e) {
    toast.type = 'error';
    toast.title = 'Gagal';
    toast.message = e?.response?.data?.message || 'Gagal menyimpan absensi.';
    toast.show = true;
  } finally {
    saving.value = false;
  }
};

const exportPdf = async () => {
    if (!selectedClassId.value || !selectedMonth.value || !selectedYearId.value) {
    toast.type = 'error';
    toast.title = 'Perhatian';
    toast.message = 'Pilih kelas, bulan, dan tahun ajaran terlebih dahulu.';
    toast.show = true;
    return;
  }

  generating.value = true;
  try {
    const res = await axios.post('/api/admin/attendances/export-pdf', {
        kelas_id: selectedClassId.value,
        bulan: selectedMonth.value,
        tahun_ajaran_id: selectedYearId.value,
        semester: selectedSemester.value
    });

    if (res.data.success) {
        window.location.href = `/api/admin/attendances/download/${res.data.filename}`;
    }
  } catch (e) {
    toast.type = 'error';
    toast.title = 'Gagal';
    toast.message = e?.response?.data?.message || 'Gagal membuat laporan PDF.';
    toast.show = true;
  } finally {
    generating.value = false;
  }
};

onMounted(async () => {
  await fetchMeta();
});

watch(selectedMonth, () => {
  if (!students.value.length) return;
  initDraft();
  loadAttendance();
});

const monthDays = computed(() => {
  if (!selectedMonth.value) return [];
  const [yearStr, monthStr] = selectedMonth.value.split('-');
  const year = Number(yearStr);
  const month = Number(monthStr);
  if (!year || !month) return [];
  const daysInMonth = new Date(year, month, 0).getDate();
  return Array.from({ length: daysInMonth }, (_, i) => {
    const day = String(i + 1).padStart(2, '0');
    return `${yearStr}-${monthStr}-${day}`;
  });
});

const dayLabel = (dateStr) => {
  if (!dateStr) return '';
  const day = dateStr.split('-')[2];
  return day;
};

const parseLocalDate = (dateStr) => {
  if (!dateStr) return null;
  const [y, m, d] = dateStr.split('-').map(Number);
  if (!y || !m || !d) return null;
  return new Date(y, m - 1, d);
};

const dayName = (dateStr) => {
  const d = parseLocalDate(dateStr);
  if (!d) return '';
  return d.toLocaleDateString('id-ID', { weekday: 'short' });
};

const isWeekend = (dateStr) => {
  const d = parseLocalDate(dateStr);
  if (!d) return false;
  return d.getDay() === 0 || d.getDay() === 6;
};

const statusClass = (status) => {
  if (status === 'I') return 'status-i';
  if (status === 'S') return 'status-s';
  if (status === 'A') return 'status-a';
  if (status === 'H') return 'status-h';
  if (status === 'L') return 'status-l';
  return 'status-empty';
};

const activeCell = ref({ studentId: null, day: null });

const openCellPopover = (studentId, day) => {
  activeCell.value = { studentId, day };
};

const isCellActive = (studentId, day) => {
  return activeCell.value.studentId === studentId && activeCell.value.day === day;
};

const setStatus = (studentId, day, status) => {
  if (attendanceDraft[studentId]?.[day]) {
    attendanceDraft[studentId][day].status = status;
  }
  activeCell.value = { studentId: null, day: null };
};

const setBulkDay = (day) => {
  bulkDay.value = bulkDay.value === day ? '' : day;
};

const clearBulkDay = () => {
  bulkDay.value = '';
};

const applyBulkStatus = (status) => {
  if (!bulkDay.value) return;
  students.value.forEach((s) => {
    if (attendanceDraft[s.id]?.[bulkDay.value]) {
      attendanceDraft[s.id][bulkDay.value].status = status;
    }
  });
  clearBulkDay();
};

const statusDisplay = (status) => {
  return status && status !== '-' ? status : '-';
};
</script>

<style scoped>
.attendance-shell {
  width: 100%;
  min-height: 100%;
  background: #f8fafc;
  color: #0f172a;
}
.attendance-container {
  max-width: 1600px;
  margin: 0 auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
@media (min-width: 768px) {
  .attendance-container { padding: 2rem; }
}
.attendance-hero {
  background: #1e40af;
  color: #ffffff;
  padding: 2rem;
  border-radius: 24px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  box-shadow: 0 20px 40px -24px rgba(30, 64, 175, 0.6);
}
.hero-title { font-size: 1.9rem; font-weight: 900; letter-spacing: -0.02em; }
.hero-subtitle {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
  margin-top: 0.3rem;
}
.hero-pill {
  padding: 0.5rem 1.1rem;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  background: rgba(255, 255, 255, 0.1);
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.attendance-filters {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  padding: 1.25rem 1.5rem;
  display: flex;
  flex-wrap: wrap;
  gap: 1rem 1.5rem;
  align-items: flex-end;
}
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  min-width: 170px;
}
.filter-group.actions { margin-left: auto; }
.form-label-compact {
  font-size: 0.7rem;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.form-input-compact {
  padding: 0.7rem 0.9rem;
  border-radius: 0.9rem;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  font-size: 0.85rem;
  font-weight: 600;
}
.btn-outline {
  padding: 0.65rem 1.1rem;
  border-radius: 0.9rem;
  border: 1px solid #e2e8f0;
  background: #f1f5f9;
  font-weight: 700;
  color: #64748b;
}

.attendance-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  overflow: hidden;
}
.attendance-card-header {
  padding: 1.25rem 1.5rem;
  background: #f8fafc;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}
.attendance-card-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1rem;
  font-weight: 800;
  color: #0f172a;
}
.attendance-badge {
  padding: 0.25rem 0.6rem;
  border-radius: 0.5rem;
  background: #ecfdf5;
  color: #047857;
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.attendance-hint {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 0.6rem;
  font-weight: 800;
  text-transform: uppercase;
  border: 1px solid #bfdbfe;
}
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.6rem;
  border-radius: 0.85rem;
  border: none;
  background: #1e40af;
  color: #ffffff;
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  cursor: pointer;
  box-shadow: 0 10px 18px -12px rgba(30, 64, 175, 0.6);
}
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.attendance-table-wrap { overflow-x: auto; }
.attendance-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.attendance-table th {
  background: #1e40af;
  color: #ffffff;
  padding: 0.7rem 0.4rem;
  font-size: 0.6rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.attendance-table th.weekend-col {
  background: #1e40af !important;
}
.attendance-table th.weekend-col .day-name {
  color: #fecdd3;
}
.attendance-table td {
  padding: 0.3rem 0.25rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.attendance-table tbody tr:nth-child(even) td { background: #f8fafc; }
.student-name { font-weight: 800; color: #0f172a; }
.student-nis {
  font-size: 0.6rem;
  font-weight: 800;
  color: #94a3b8;
  text-transform: uppercase;
}
.no-col, .no-cell {
  width: 56px;
  min-width: 56px;
  text-align: center;
  font-weight: 700;
  color: #94a3b8;
  position: sticky;
  left: 0;
  background: #ffffff;
  z-index: 3;
  border-right: 1px solid #e2e8f0;
}
.student-col, .student-cell {
  min-width: 240px;
  position: sticky;
  left: 56px;
  background: #ffffff;
  z-index: 2;
}
.student-cell { z-index: 1; }
.day-col { min-width: 48px; text-align: center; cursor: pointer; }
.day-name {
  display: block;
  margin-top: 0.1rem;
  font-size: 0.55rem;
  opacity: 0.6;
}
.day-cell { text-align: center; position: relative; }
.cell-wrap { position: relative; display: inline-flex; justify-content: center; }
.cell-popover {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  padding: 0.4rem;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.35rem;
  box-shadow: 0 18px 28px -20px rgba(15, 23, 42, 0.45);
  z-index: 20;
  min-width: 140px;
}
.pop-btn {
  border: 1px solid transparent;
  border-radius: 0.6rem;
  padding: 0.35rem 0.4rem;
  font-size: 0.6rem;
  font-weight: 800;
  text-transform: uppercase;
  cursor: pointer;
}
.attendance-btn {
  width: 32px;
  height: 32px;
  border-radius: 0.6rem;
  border: 1px solid #e2e8f0;
  font-size: 0.65rem;
  font-weight: 800;
  background: #ffffff;
  cursor: pointer;
}
.status-empty { background: #f8fafc; color: #94a3b8; border-color: #e2e8f0; }
.status-h { background: #ecfdf5; color: #059669; border-color: #d1fae5; }
.status-i { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
.status-s { background: #fff7ed; color: #d97706; border-color: #fed7aa; }
.status-a { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
.status-l { background: #f3f4f6; color: #4b5563; border-color: #e5e7eb; }
.weekend-col { background: rgba(15, 23, 42, 0.06); }
.selected-column {
  background: transparent !important;
  box-shadow: inset 0 0 0 1px rgba(30, 64, 175, 0.25);
}

.legend {
  display: flex;
  gap: 1.5rem;
  justify-content: center;
  padding: 0.9rem;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
  font-size: 0.6rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #64748b;
}
.legend-item { display: inline-flex; align-items: center; gap: 0.4rem; }
.dot { width: 0.6rem; height: 0.6rem; border-radius: 999px; }
.dot-h { background: #10b981; }
.dot-i { background: #3b82f6; }
.dot-s { background: #f59e0b; }
.dot-a { background: #f43f5e; }
.dot-l { background: #9ca3af; }
.empty-state {
  text-align: center;
  padding: 2rem;
  color: #94a3b8;
  font-weight: 600;
}


.bulk-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.25);
  backdrop-filter: blur(2px);
  z-index: 49;
}
.bulk-toolbar {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 300px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 1.25rem;
  padding: 1rem;
  box-shadow: 0 24px 40px -20px rgba(15, 23, 42, 0.4);
  opacity: 0;
  pointer-events: none;
  transition: all 0.2s;
  z-index: 50;
}
.bulk-panel-active .bulk-toolbar {
  transform: translate(-50%, -50%);
  opacity: 1;
  pointer-events: auto;
}
.bulk-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.6rem; }
.bulk-title { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: #64748b; display: flex; align-items: center; gap: 0.4rem; }
.pulse-dot { width: 0.4rem; height: 0.4rem; border-radius: 999px; background: #1e40af; animation: pulse 1.5s infinite; }
.bulk-subtitle { font-size: 0.7rem; color: #94a3b8; margin-bottom: 0.75rem; }
.bulk-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; margin-bottom: 0.75rem; }
.bulk-btn { padding: 0.5rem; border-radius: 0.75rem; font-size: 0.65rem; font-weight: 800; border: 1px solid transparent; }
.bulk-cancel { width: 100%; padding: 0.6rem; border-radius: 0.75rem; border: none; background: #0f172a; color: #ffffff; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; }
.bulk-close { background: transparent; border: none; color: #94a3b8; }
@keyframes pulse {
  0% { opacity: 0.4; }
  50% { opacity: 1; }
  100% { opacity: 0.4; }
}
</style>
