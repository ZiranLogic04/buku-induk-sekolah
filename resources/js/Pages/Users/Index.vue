<template>
  <div class="users-page">
    <div class="header-blue">
      <div>
        <h1 class="page-title">Manajemen User</h1>
        <p class="page-subtitle">Kelola akun pengguna untuk akses sistem.</p>
      </div>
      <button class="btn-primary header-btn" @click="openModal()">
        <span class="material-symbols-outlined icon">person_add</span>
        Tambah User
      </button>
    </div>

    <div class="filter-bar">
      <div class="filter-item search">
        <span class="material-symbols-outlined">search</span>
        <input v-model="searchQuery" type="text" placeholder="Cari nama atau email..." />
      </div>
      <div class="filter-meta">Total <strong>{{ filteredUsers.length }}</strong> User</div>
    </div>

    <div class="card table-card">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Role</th>
              <th class="text-right pr-6">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(user, idx) in filteredUsers" :key="user.id" class="hover-row">
              <td class="td-left text-xs text-muted font-bold">{{ idx + 1 }}</td>
              <td class="td-left">
                <div class="user-name">{{ user.name }}</div>
              </td>
              <td class="td-left text-sm text-slate-600">{{ user.email }}</td>
              <td class="td-left">
                <span class="role-badge" :class="roleBadge(user.role)">
                  {{ user.role || 'operator' }}
                </span>
              </td>
              <td class="td-right text-right pr-6">
                <div class="action-buttons">
                  <button class="btn-icon hover-amber" title="Edit" @click="openModal(user)">
                    <span class="material-symbols-outlined">edit</span>
                  </button>
                  <button class="btn-icon hover-red" title="Delete" @click="confirmDelete(user)">
                    <span class="material-symbols-outlined">delete</span>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredUsers.length === 0">
              <td colspan="5" class="empty-state">
                <span class="material-symbols-outlined empty-icon">person_off</span>
                <p>Belum ada data user.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="modal-backdrop">
      <div class="modal-panel">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon">
              <span class="material-symbols-outlined">person_add</span>
            </div>
            <div>
              <h3 class="modal-title">{{ isEdit ? 'Edit User' : 'Tambah User' }}</h3>
              <p class="modal-subtitle">Kelola akun pengguna untuk akses sistem.</p>
            </div>
          </div>
          <button class="btn-close" @click="closeModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group full">
              <label>Nama</label>
              <input v-model="form.name" type="text" class="form-input" placeholder="Nama lengkap" />
            </div>
            <div class="form-group full">
              <label>Email</label>
              <input v-model="form.email" type="email" class="form-input" placeholder="email@contoh.com" />
            </div>
            <div class="form-group full">
              <label>Password</label>
              <input v-model="form.password" type="password" class="form-input" :placeholder="isEdit ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter'" />
              <p class="helper-text" v-if="isEdit">Kosongkan untuk mempertahankan password lama.</p>
            </div>
            <div class="form-group full">
              <label>Role</label>
              <select v-model="form.role" class="form-input" disabled>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="closeModal">Batal</button>
          <button class="btn-primary" @click="submitForm">{{ isEdit ? 'Simpan' : 'Tambah' }}</button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <div v-if="showDeleteModal" class="modal-backdrop">
      <div class="modal-panel modal-xs">
        <div class="modal-body-center">
          <div class="danger-icon">
            <span class="material-symbols-outlined">warning</span>
          </div>
          <h3 class="modal-title">Hapus User?</h3>
          <p class="modal-text">Data yang dihapus tidak bisa dikembalikan.</p>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showDeleteModal = false">Batal</button>
          <button class="btn-danger" @click="deleteUser">Hapus</button>
        </div>
      </div>
    </div>

    <Toast :show="toast.show" :type="toast.type" :title="toast.title" :message="toast.message" @close="toast.show = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import Toast from '@/Components/Toast.vue';

const users = ref([]);
const searchQuery = ref('');

const showModal = ref(false);
const showDeleteModal = ref(false);
const isEdit = ref(false);
const editId = ref(null);
const deleteId = ref(null);

const toast = reactive({
  show: false,
  type: 'success',
  title: '',
  message: ''
});

const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'admin'
});

const filteredUsers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return users.value;
  return users.value.filter((u) => {
    const name = (u.name || '').toLowerCase();
    const email = (u.email || '').toLowerCase();
    return name.includes(q) || email.includes(q);
  });
});

const roleBadge = (role) => {
  return role === 'admin' ? 'role-admin' : 'role-operator';
};

const fetchUsers = async () => {
  const res = await axios.get('/api/admin/users');
  users.value = res.data || [];
};

const openModal = (user = null) => {
  isEdit.value = !!user;
  editId.value = user?.id || null;
  form.name = user?.name || '';
  form.email = user?.email || '';
  form.password = '';
  form.role = user?.role || 'admin';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitForm = async () => {
  try {
    if (isEdit.value) {
      await axios.put(`/api/admin/users/${editId.value}`, form);
      toast.type = 'success';
      toast.title = 'Berhasil';
      toast.message = 'User diperbarui.';
    } else {
      await axios.post('/api/admin/users', form);
      toast.type = 'success';
      toast.title = 'Berhasil';
      toast.message = 'User ditambahkan.';
    }
    toast.show = true;
    showModal.value = false;
    await fetchUsers();
  } catch (err) {
    toast.type = 'error';
    toast.title = 'Gagal';
    toast.message = err?.response?.data?.message || 'Gagal menyimpan data.';
    toast.show = true;
  }
};

const confirmDelete = (user) => {
  deleteId.value = user.id;
  showDeleteModal.value = true;
};

const deleteUser = async () => {
  try {
    await axios.delete(`/api/admin/users/${deleteId.value}`);
    showDeleteModal.value = false;
    toast.type = 'success';
    toast.title = 'Berhasil';
    toast.message = 'User dihapus.';
    toast.show = true;
    await fetchUsers();
  } catch (err) {
    toast.type = 'error';
    toast.title = 'Gagal';
    toast.message = err?.response?.data?.message || 'Gagal menghapus data.';
    toast.show = true;
  }
};

onMounted(async () => {
  await fetchUsers();
});
</script>

<style scoped>
.users-page {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
@media (min-width: 768px) { .users-page { padding: 2rem; } }

.header-blue {
  background: var(--color-primary, #1e40af);
  color: #ffffff;
  padding: 1.5rem 1.75rem;
  border-radius: 1.5rem;
  box-shadow: 0 12px 24px -18px rgba(30, 64, 175, 0.6);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.page-title {
  font-size: 1.55rem;
  font-weight: 900;
  color: #ffffff;
  letter-spacing: -0.025em;
  margin-bottom: 0.35rem;
}
.page-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.8rem;
  font-weight: 500;
}
.header-btn {
  border-radius: 999px;
  padding: 0.6rem 1.1rem;
  font-size: 0.85rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: #ffffff;
  color: var(--color-primary, #1e40af);
  border: 1px solid rgba(255, 255, 255, 0.6);
  box-shadow: 0 6px 14px -8px rgba(15, 23, 42, 0.4);
}
@media (min-width: 768px) {
  .header-blue { flex-direction: row; align-items: center; justify-content: space-between; }
}

.filter-bar {
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
.filter-meta {
  margin-left: auto;
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 700;
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
.td-left { text-align: left; }
.td-right { text-align: right; }
.pr-6 { padding-right: 1.5rem; }
.user-name { font-weight: 800; color: #0f172a; }
.text-sm { font-size: 0.875rem; }
.text-xs { font-size: 0.75rem; }
.text-slate-600 { color: #475569; }
.text-muted { color: #64748b; }

.role-badge {
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
}
.role-admin { background: #e0e7ff; color: #4338ca; }
.role-operator { background: #ecfdf5; color: #047857; }

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background-color: var(--color-primary);
  color: white;
  padding: 0.6rem 1.1rem;
  border-radius: 0.9rem;
  font-weight: 800;
  font-size: 0.75rem;
  border: none;
  cursor: pointer;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.btn-secondary {
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  font-weight: 700;
  font-size: 0.8125rem;
  color: #475569;
  background: #f1f5f9;
  border: none;
  cursor: pointer;
}
.icon { font-size: 1.1rem; }

.action-buttons { display: flex; align-items: center; gap: 0.25rem; justify-content: flex-end; }
.btn-icon {
  padding: 0.5rem;
  border-radius: 0.75rem;
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}
.btn-icon:hover { color: var(--color-primary); background-color: rgba(30, 64, 175, 0.05); }
.btn-icon.hover-amber:hover { color: #d97706; background-color: #fef3c7; }
.btn-icon.hover-red:hover { color: #ef4444; background-color: #fef2f2; }

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #94a3b8;
  font-weight: 600;
}
.empty-icon { font-size: 1.6rem; color: #cbd5f5; margin-bottom: 0.4rem; }

/* Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.modal-panel {
  background: white;
  width: 100%;
  max-width: 700px;
  border-radius: 2.5rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
}
.modal-xs { max-width: 400px; }
.modal-header {
  padding: 1.25rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f1f5f9;
}
.modal-header-left { display: flex; align-items: center; gap: 0.9rem; }
.modal-icon {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 1rem;
  background: #4f46e5;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
}
.modal-title { font-size: 1.05rem; font-weight: 800; color: #0f172a; }
.modal-subtitle { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
.btn-close {
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  color: #64748b;
  border: none;
  background: #f8fafc;
  cursor: pointer;
}
.modal-body { padding: 2rem; }
.modal-body-center { padding: 1.5rem; text-align: center; }
.modal-text { font-size: 0.875rem; color: #64748b; }
.modal-footer {
  padding: 1.25rem 2rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
}

.form-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-group label { font-size: 0.8125rem; font-weight: 700; color: #334155; }
.form-input {
  padding: 0.8rem 1rem;
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  font-weight: 600;
}
.helper-text { font-size: 0.7rem; color: #94a3b8; font-weight: 600; }
.danger-icon {
  width: 3rem;
  height: 3rem;
  margin: 0 auto 0.75rem;
  border-radius: 1rem;
  background: #fee2e2;
  color: #ef4444;
  display: flex;
  align-items: center;
  justify-content: center;
}
.form-group.full { grid-column: 1 / -1; }

.btn-danger {
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  font-weight: 700;
  font-size: 0.8125rem;
  color: white;
  background: #ef4444;
  border: none;
  cursor: pointer;
}
</style>
