<template>
  <div class="print-shell">    <div class="print-container">
      <div class="print-hero">
        <div>
          <h1 class="print-title">Cetak Buku Induk</h1>
          <p class="print-subtitle">Generate dan unduh buku induk siswa per kelas.</p>
        </div>
        <div class="print-pill">
          Tahun Ajaran: {{ activeYearName || '-' }}
        </div>
      </div>

      <div class="print-filter-card">
        <div class="filter-row">
          <div class="filter-group" style="flex: 1;">
            <label class="form-label-compact">Pilih Kelas</label>
            <select class="form-input-compact" v-model="selectedClassId" @change="loadStudents">
              <option value="">Pilih Kelas</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.nama }}</option>
            </select>
          </div>
          <div class="filter-actions" v-if="students.length > 0" style="display: flex; gap: 0.5rem; align-items: flex-end; padding-bottom: 2px;">
            <button class="btn-action-solid" @click="generateAll" :disabled="generatingAll" style="padding: 0.65rem 1rem; font-size: 0.75rem;">
              {{ generatingAll ? 'Memproses...' : 'Generate Semua' }}
            </button>
            <button class="btn-action-outline" @click="downloadAll" :disabled="students.length === 0 || generatingAll" style="padding: 0.65rem 1rem; font-size: 0.75rem;">
              Download Semua
            </button>
          </div>
        </div>
      </div>

      <div class="print-table-card">
        <div class="table-header">
          <h3 class="table-title">Daftar Siswa</h3>
          <span class="table-badge">Total: {{ students.length }} Siswa</span>
        </div>
        <div class="table-wrap">
          <table class="print-table">
            <thead>
              <tr>
                <th class="w-no">No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>JK</th>
                <th>Terakhir Generate</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody v-if="students.length">
              <tr v-for="(s, idx) in students" :key="s.id">
                <td class="text-muted">{{ String(idx + 1).padStart(2, '0') }}</td>
                <td class="text-strong">{{ s.nis || '-' }}</td>
                <td class="text-name">{{ s.nama }}</td>
                <td class="text-muted">{{ s.jenis_kelamin || '-' }}</td>
                <td class="text-muted italic">{{ s.last_generated_at ? formatDate(s.last_generated_at) : 'Belum Pernah' }}</td>
                <td class="text-center">
                  <div class="btn-group">
                    <button class="btn-action-solid btn-row" @click="generate(s)" :disabled="s._generating">
                      {{ s._generating ? '...' : 'Generate' }}
                    </button>
                    <button class="btn-action-outline btn-row" :disabled="!s._filename" @click="download(s)">Download</button>
                  </div>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="6" class="empty-state">Pilih kelas untuk melihat siswa.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const classes = ref([]);
const students = ref([]);
const selectedClassId = ref('');
const activeYearName = ref('');
const generatingAll = ref(false);
const classFilename = ref('');

const fetchMeta = async () => {
  const res = await axios.get('/api/admin/classes');
  classes.value = res.data.kelas || [];
  activeYearName.value = res.data.session_academic_year || res.data.lembaga?.tahun_ajaran || '';
};

const loadStudents = async () => {
  classFilename.value = '';
  if (!selectedClassId.value) {
    students.value = [];
    return;
  }
  const res = await axios.get('/api/admin/students', {
    params: { kelas_id: selectedClassId.value, nopaginate: true }
  });
  students.value = (res.data.students || []).map(s => ({
    ...s,
    _generating: false,
    _filename: s.last_generated_filename || null,
  }));
};

// Generate PDF for a single student (save to server)
const generate = async (student) => {
  student._generating = true;
  try {
    const res = await axios.post(`/admin/print-books/generate/${student.id}`, {}, {
      headers: { 'X-Loading-Text': 'MEMUAT BUKU INDUK...' }
    });
    student._filename = res.data.filename;
    student.last_generated_at = new Date().toISOString();
  } catch (err) {
    alert('Gagal generate PDF: ' + (err.response?.data?.message || err.message));
  } finally {
    student._generating = false;
  }
};

// Download a previously generated PDF
const download = (student) => {
  if (!student._filename) return;
  window.open(`/admin/print-books/download/${student._filename}`, '_blank');
};

// Generate PDF for ALL students in the class (Individual files)
const generateAll = async () => {
  if (!selectedClassId.value || students.value.length === 0) return;
  if (!confirm(`Sistem akan memproses ${students.value.length} siswa. Ini mungkin memakan waktu beberapa menit. Lanjutkan?`)) return;
  
  generatingAll.value = true;
  try {
    for (const student of students.value) {
      await generate(student);
    }
    alert('Selesai! Seluruh data siswa telah di-generate secara individual.');
  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan saat memproses masal.');
  } finally {
    generatingAll.value = false;
  }
};

// Download the class PDF (merged)
const downloadAll = async () => {
  if (!selectedClassId.value) return;
  generatingAll.value = true;
  try {
    const res = await axios.post(`/admin/print-books/generate-class/${selectedClassId.value}`, {}, {
      headers: { 'X-Loading-Text': 'MENGGABUNGKAN PDF SEKELAS...' }
    });
    window.open(`/admin/print-books/download/${res.data.filename}`, '_blank');
  } catch (err) {
    alert('Gagal menggabungkan PDF: ' + (err.response?.data?.message || err.message));
  } finally {
    generatingAll.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

onMounted(async () => {
  await fetchMeta();
});
</script>

<style scoped>
.print-shell { width: 100%; min-height: 100%; background: #f8fafc; color: #0f172a; }
.print-container { max-width: 1400px; margin: 0 auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1.5rem; }
@media (min-width: 768px) { .print-container { padding: 2rem; } }
.print-hero { background: #1e40af; color: #fff; padding: 1.5rem 1.75rem; border-radius: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 20px 40px -24px rgba(30,64,175,0.6); }
.print-title { font-size: 1.6rem; font-weight: 900; letter-spacing: -0.02em; }
.print-subtitle { font-size: 0.85rem; color: rgba(255,255,255,0.8); margin-top: 0.2rem; }
.print-pill { padding: 0.35rem 0.9rem; border-radius: 999px; border: 1px solid rgba(255,255,255,0.25); background: rgba(255,255,255,0.1); font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; }
.print-filter-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1.5rem; padding: 1.25rem 1.5rem; }
.filter-row { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem; }
.filter-group { flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 0.4rem; }
.form-label-compact { font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.08em; }
.form-input-compact { width: 100%; padding: 0.65rem 0.9rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.9rem; font-size: 0.85rem; font-weight: 600; }
.print-table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1.5rem; overflow: hidden; }
.table-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.table-title { font-size: 1rem; font-weight: 800; color: #0f172a; }
.table-badge { padding: 0.3rem 0.75rem; background: #eff6ff; color: #2563eb; border-radius: 999px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; }
.table-wrap { overflow-x: auto; }
.print-table { width: 100%; border-collapse: collapse; text-align: left; }
.print-table th { background: #1e40af; color: #fff; padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; }
.print-table td { padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; }
.w-no { width: 64px; }
.text-muted { color: #94a3b8; font-weight: 700; font-size: 0.75rem; }
.text-strong { font-weight: 800; color: #475569; }
.text-name { font-weight: 800; color: #0f172a; text-transform: uppercase; }
.text-center { text-align: center; }
.italic { font-style: italic; }
.btn-group { display: flex; gap: 0.5rem; justify-content: center; }
.btn-action-solid { padding: 0.35rem 0.8rem; background: #1e40af; color: #fff; border: none; border-radius: 0.75rem; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; cursor: pointer; transition: all 0.15s; }
.btn-action-solid:hover:not(:disabled) { background: #1e3a8a; }
.btn-action-solid:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-action-outline { padding: 0.35rem 0.8rem; border: 2px solid #1e40af; color: #1e40af; background: #fff; border-radius: 0.75rem; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; cursor: pointer; transition: all 0.15s; }
.btn-action-outline:hover:not(:disabled) { background: #eff6ff; }
.btn-action-outline:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-row { }
.empty-state { text-align: center; padding: 2rem; color: #94a3b8; font-weight: 600; }
</style>
