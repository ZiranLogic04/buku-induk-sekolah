<template>
  <div class="grades-shell">
    <div class="grades-container">
      <div class="grades-hero">
        <div>
          <h1 class="grades-title">Input Nilai</h1>
          <p class="grades-subtitle">Manajemen penilaian harian dan raport siswa.</p>
        </div>
        <div class="grades-pill">
          Tahun Ajaran: {{ selectedYear?.nama || '-' }}
        </div>
      </div>

      <div class="grades-filters">
        <div class="filter-group">
          <label>Kelas</label>
          <select v-model="selectedClassId" @change="loadStudents">
            <option value="">Pilih Kelas</option>
            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.nama }}</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Semester</label>
          <div class="filter-display">{{ selectedSemester }}</div>
        </div>
        <div class="filter-group">
          <label>Kurikulum</label>
          <div class="filter-display">{{ kurikulum }}</div>
        </div>
        <div class="filter-group subject-group">
          <label>Mapel</label>
          <select v-model="selectedSubjectId" @change="loadGrades">
            <option value="">Pilih Mapel</option>
            <option v-for="sub in filteredSubjects" :key="sub.id" :value="String(sub.id)">{{ sub.nama }}</option>
          </select>
        </div>
      </div>

      <div class="grades-card">
        <div class="grades-card-header">
          <div class="grades-card-title">
            <span>Daftar Penilaian</span>
            <span class="grades-badge">{{ saving ? 'Menyimpan...' : 'Siap disimpan' }}</span>
          </div>
          <button class="grades-save" type="button" :disabled="saving" @click="saveAllGrades">
            <span class="material-symbols-outlined">save</span>
            Simpan Nilai
          </button>
        </div>

        <div class="grades-table-wrap">
          <table class="grades-table">
            <thead>
              <tr>
                <th class="student-col">Siswa</th>
                <th v-if="!isK13" class="grade-col">Nilai</th>
                <th v-if="!isK13" class="grade-col">Keterangan</th>
                <th v-if="isK13" class="grade-col">Nilai Pengetahuan</th>
                <th v-if="isK13" class="grade-col">Ket. Pengetahuan</th>
                <th v-if="isK13" class="grade-col">Nilai Keterampilan</th>
                <th v-if="isK13" class="grade-col">Ket. Keterampilan</th>
              </tr>
            </thead>
            <tbody v-if="students.length && selectedSubjectId">
              <tr v-for="student in students" :key="student.id">
                <td class="student-cell">
                  <div class="student-nis">{{ student.nis || '-' }}</div>
                  <div class="student-name">{{ student.nama }}</div>
                </td>
                <td v-if="!isK13" class="grade-cell">
                  <input v-model="gradeDraft[student.id].nilai_angka" class="grade-input" type="number" min="0" max="100" placeholder="0" />
                </td>
                <td v-if="!isK13" class="grade-cell">
                  <input v-model="gradeDraft[student.id].keterangan" class="desc-input" type="text" placeholder="Catatan..." />
                </td>
                <td v-if="isK13" class="grade-cell">
                  <input v-model="gradeDraft[student.id].nilai_pengetahuan" class="grade-input" type="number" min="0" max="100" placeholder="0" />
                </td>
                <td v-if="isK13" class="grade-cell">
                  <input v-model="gradeDraft[student.id].ket_pengetahuan" class="desc-input" type="text" placeholder="Catatan..." />
                </td>
                <td v-if="isK13" class="grade-cell">
                  <input v-model="gradeDraft[student.id].nilai_keterampilan" class="grade-input" type="number" min="0" max="100" placeholder="0" />
                </td>
                <td v-if="isK13" class="grade-cell">
                  <input v-model="gradeDraft[student.id].ket_keterampilan" class="desc-input" type="text" placeholder="Catatan..." />
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td :colspan="isK13 ? 5 : 3" class="empty-cell">Pilih kelas dan mapel untuk melihat siswa.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <Toast :show="toast.show" :type="toast.type" :title="toast.title" :message="toast.message" @close="toast.show = false" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import Toast from '@/Components/Toast.vue';

const classes = ref([]);
const tahunAjarans = ref([]);
const subjects = ref([]);
const jurusans = ref([]);
const students = ref([]);
const kurikulum = ref('Kurikulum Merdeka');

const selectedClassId = ref('');
const selectedYearId = ref('');
const selectedSemester = ref('Ganjil');
const selectedSubjectId = ref('');
const saving = ref(false);

const gradeDraft = reactive({});
const toast = reactive({ show: false, type: 'success', title: '', message: '' });

const selectedClass = computed(() => classes.value.find((c) => c.id === Number(selectedClassId.value)));
const selectedYear = computed(() => tahunAjarans.value.find((t) => t.id === Number(selectedYearId.value)));
const isK13 = computed(() => kurikulum.value === 'Kurikulum 2013');

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
  const jurusanOk = !subjectJurusan || subjectJurusan === 'UMUM' || subjectJurusan === jurusanName;

  return tingkatOk && jurusanOk;
};

const filteredSubjects = computed(() => {
  if (!selectedClass.value) return [];
  return subjects.value
    .filter((s) => subjectMatchesClass(s, selectedClass.value))
    .sort((a, b) => (a.urutan || 0) - (b.urutan || 0));
});

const resetDraft = () => {
  Object.keys(gradeDraft).forEach((k) => delete gradeDraft[k]);
  students.value.forEach((student) => {
    gradeDraft[student.id] = {
      nilai_angka: '',
      nilai_pengetahuan: '',
      nilai_keterampilan: '',
      keterangan: '',
      ket_pengetahuan: '',
      ket_keterampilan: ''
    };
  });
};

const ensureDraft = (studentId) => {
  if (!gradeDraft[studentId]) {
    gradeDraft[studentId] = {
      nilai_angka: '',
      nilai_pengetahuan: '',
      nilai_keterampilan: '',
      keterangan: '',
      ket_pengetahuan: '',
      ket_keterampilan: ''
    };
  }
};

const fetchMeta = async () => {
  const [classesRes, subjectsRes, lembagaRes, meRes] = await Promise.all([
    axios.get('/api/admin/classes'),
    axios.get('/api/admin/subjects'),
    axios.get('/api/admin/lembaga'),
    axios.get('/api/me')
  ]);

    classes.value = classesRes.data.kelas || [];
    jurusans.value = classesRes.data.jurusans || [];
    tahunAjarans.value = classesRes.data.tahun_ajarans || [];
    subjects.value = subjectsRes.data.subjects || [];
    kurikulum.value = lembagaRes.data?.lembaga?.kurikulum || 'Kurikulum Merdeka';

    const sessionContext = meRes.data?.context;
    if (sessionContext?.semester) selectedSemester.value = sessionContext.semester;
    if (sessionContext?.academic_year && tahunAjarans.value.length) {
      const match = tahunAjarans.value.find((t) => t.nama === sessionContext.academic_year);
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
  selectedSubjectId.value = '';
  if (!selectedClassId.value) {
    students.value = [];
    resetDraft();
    return;
  }
  if (!selectedYearId.value) {
    students.value = [];
    toast.type = 'warning';
    toast.title = 'Tahun Ajaran';
    toast.message = 'Tahun ajaran aktif belum terdeteksi di lembaga.';
    toast.show = true;
    return;
  }
  
  const res = await axios.get('/api/admin/students', {
    params: { kelas_id: selectedClassId.value, nopaginate: true }
  });
  students.value = res.data.students || [];
  resetDraft();
  await loadGrades();
};

const loadGrades = async () => {
  if (!selectedClassId.value || !selectedYearId.value || !selectedSemester.value) return;
  if (!selectedSubjectId.value) {
    resetDraft();
    return;
  }
  
  const res = await axios.get('/api/admin/grades', {
    params: {
      kelas_id: selectedClassId.value,
      tahun_ajaran_id: selectedYearId.value,
      semester: selectedSemester.value
    }
  });
    const grades = res.data || [];
    resetDraft();
    grades.forEach((grade) => {
      if (!grade.student_id || !grade.items) return;
      grade.items.forEach((item) => {
        if (String(item.subject_id) !== selectedSubjectId.value) return;
        ensureDraft(grade.student_id);
        gradeDraft[grade.student_id].nilai_angka = formatNumberDisplay(item.nilai_angka);
        gradeDraft[grade.student_id].nilai_pengetahuan = formatNumberDisplay(item.nilai_pengetahuan);
        gradeDraft[grade.student_id].nilai_keterampilan = formatNumberDisplay(item.nilai_keterampilan);
        gradeDraft[grade.student_id].keterangan = item.keterangan ?? '';
        gradeDraft[grade.student_id].ket_pengetahuan = item.keterangan_pengetahuan ?? '';
        gradeDraft[grade.student_id].ket_keterampilan = item.keterangan_keterampilan ?? '';
    });
  });
};

const normalizeNumber = (value) => {
  if (value === '' || value === null || value === undefined) return null;
  const num = Number(value);
  return Number.isNaN(num) ? null : num;
};

const formatNumberDisplay = (value) => {
  if (value === '' || value === null || value === undefined) return '';
  const num = Number(value);
  if (Number.isNaN(num)) return String(value);
  return Number.isInteger(num) ? String(num) : String(num);
};

const buildItemForStudent = (studentId) => {
  if (!selectedSubjectId.value) return null;
  const draft = gradeDraft[studentId] || {};
  const nilaiAngka = normalizeNumber(draft.nilai_angka);
  const nilaiPengetahuan = normalizeNumber(draft.nilai_pengetahuan);
  const nilaiKeterampilan = normalizeNumber(draft.nilai_keterampilan);
  const ket = draft.keterangan ? String(draft.keterangan).trim() : '';
  const ketPen = draft.ket_pengetahuan ? String(draft.ket_pengetahuan).trim() : '';
  const ketKet = draft.ket_keterampilan ? String(draft.ket_keterampilan).trim() : '';

  if (isK13.value) {
    return {
      subject_id: Number(selectedSubjectId.value),
      nilai_angka: null,
      nilai_pengetahuan: nilaiPengetahuan,
      nilai_keterampilan: nilaiKeterampilan,
      keterangan: null,
      keterangan_pengetahuan: ketPen || null,
      keterangan_keterampilan: ketKet || null
    };
  }

  return {
    subject_id: Number(selectedSubjectId.value),
    nilai_angka: nilaiAngka,
    nilai_pengetahuan: null,
    nilai_keterampilan: null,
    keterangan: ket || null,
    keterangan_pengetahuan: null,
    keterangan_keterampilan: null
  };
};

const saveAllGrades = async () => {
  if (!selectedYearId.value || !selectedClassId.value) return;
  if (!selectedSubjectId.value) {
    toast.type = 'warning';
    toast.title = 'Mapel';
    toast.message = 'Pilih mapel terlebih dahulu.';
    toast.show = true;
    return;
  }

  const payloads = students.value.map((student) => ({
    student_id: student.id,
    tahun_ajaran_id: selectedYearId.value,
    semester: selectedSemester.value,
    kelas_id: selectedClassId.value,
    items: [buildItemForStudent(student.id)],
    replace_items: false
  }));

  saving.value = true;
  try {
    await Promise.all(payloads.map((payload) => axios.post('/api/admin/grades', payload)));
    toast.type = 'success';
    toast.title = 'Berhasil';
    toast.message = 'Nilai berhasil disimpan.';
    toast.show = true;
  } catch (e) {
    toast.type = 'error';
    toast.title = 'Gagal';
    toast.message = e?.response?.data?.message || 'Gagal menyimpan nilai.';
    toast.show = true;
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  await fetchMeta();
});
</script>

<style scoped>
.grades-shell {
  width: 100%;
  background: #f8fafc;
  min-height: 100%;
  color: #0f172a;
}
.grades-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
@media (min-width: 768px) {
  .grades-container { padding: 2rem; }
}
.grades-hero {
  background: #1e40af;
  color: #ffffff;
  padding: 1.5rem 1.75rem;
  border-radius: 1.5rem;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  box-shadow: 0 20px 40px -24px rgba(30, 64, 175, 0.6);
}
.grades-title {
  font-size: 1.6rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: -0.02em;
}
.grades-subtitle {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.85);
  margin-top: 0.2rem;
}
.grades-pill {
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  background: rgba(255, 255, 255, 0.1);
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.grades-filters {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 1.5rem;
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
  min-width: 180px;
}
.subject-group { min-width: 220px; }
.filter-group label {
  font-size: 0.7rem;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.filter-group select {
  padding: 0.6rem 0.75rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  font-size: 0.85rem;
  font-weight: 600;
}
.filter-display {
  padding: 0.6rem 0.75rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  background: #f1f5f9;
  font-size: 0.85rem;
  font-weight: 700;
  color: #64748b;
}

.grades-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 1.5rem;
  overflow: hidden;
}
.grades-card-header {
  padding: 1rem 1.5rem;
  background: #f8fafc;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}
.grades-card-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.85rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #0f172a;
}
.grades-badge {
  padding: 0.25rem 0.6rem;
  border-radius: 0.5rem;
  background: #ecfdf5;
  color: #047857;
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.grades-save {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1rem;
  border-radius: 0.9rem;
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
.grades-save:disabled { opacity: 0.6; cursor: not-allowed; }

.grades-table-wrap { overflow-x: auto; }
.grades-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.grades-table th {
  background: #1e40af;
  color: #ffffff;
  padding: 0.9rem 1.2rem;
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
}
.grades-table td {
  padding: 0.9rem 1.2rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
}
.grades-table tbody tr:nth-child(even) td { background: #f8fafc; }
.student-col, .student-cell {
  min-width: 200px;
  position: sticky;
  left: 0;
  background: #ffffff;
  z-index: 2;
}
.student-cell {
  z-index: 1;
}
.student-nis {
  font-size: 0.6rem;
  font-weight: 800;
  color: #94a3b8;
  text-transform: uppercase;
}
.student-name {
  font-size: 0.9rem;
  font-weight: 800;
  color: #0f172a;
}
.grade-col { min-width: 160px; }
.grade-col, .grade-cell { text-align: center; }
.grade-cell { vertical-align: middle; }
.grade-input {
  width: 70px;
  height: 36px;
  text-align: center;
  font-size: 0.75rem;
  font-weight: 800;
  border: 1px solid #e2e8f0;
  border-radius: 0.6rem;
  background: #ffffff;
}
.desc-input {
  min-width: 180px;
  height: 36px;
  padding: 0 0.6rem;
  font-size: 0.7rem;
  font-weight: 700;
  border: 1px solid #e2e8f0;
  border-radius: 0.6rem;
  background: #ffffff;
}

.empty-cell {
  padding: 2rem;
  text-align: center;
  color: #94a3b8;
  font-weight: 600;
}
</style>
