<template>
  <div class="login-page">
    <main class="login-main">
      <div class="login-card">
        <div class="login-header">
          <div class="logo-box">
            <span class="material-symbols-outlined logo-icon">school</span>
          </div>
          <h1 class="login-title">Buku Induk Sekolah</h1>
          <p class="login-subtitle">Masuk ke Portal Administrasi</p>
        </div>

        <form class="login-form" @submit.prevent="handleLogin">
          <div class="field">
            <label for="email">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="nama@sekolah.sch.id"
              required
            />
          </div>

          <div class="field">
            <label for="password">Kata Sandi</label>
            <div class="password-wrap">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                required
              />
              <button class="toggle-btn" type="button" aria-label="Lihat kata sandi" @click="showPassword = !showPassword">
                <span class="material-symbols-outlined">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
              </button>
            </div>
          </div>

          <div class="grid-two">
            <div class="field">
              <label for="year">Tahun Ajaran</label>
              <select id="year" v-model="form.academicYear">
                <option v-for="ta in tahunAjaranOptions" :key="ta.id" :value="ta.nama">
                  {{ ta.nama }}
                </option>
              </select>
            </div>
            <div class="field">
              <label for="semester">Semester</label>
              <select id="semester" v-model="form.semester">
                <option value="Ganjil">Ganjil</option>
                <option value="Genap">Genap</option>
              </select>
            </div>
          </div>

          <div class="actions">
            <button class="submit-btn" type="submit" :disabled="isLoading">Masuk</button>
            <div class="help-link">
              <a href="#">Lupa kata sandi?</a>
            </div>
          </div>
        </form>

        <!-- Error message shown via toast -->
      </div>
    </main>

    <div v-if="toast.show" class="toast" :class="toast.type">
      <span class="material-symbols-outlined">{{ toast.icon }}</span>
      <span class="toast-text">{{ toast.message }}</span>
    </div>

    <footer class="login-footer">� 2024 Sistem Informasi Sekolah</footer>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { resetAuthCache } from '@/router';
import axios from 'axios';

const router = useRouter();

const form = reactive({
  email: '',
  password: '',
  academicYear: '2023/2024',
  semester: 'Ganjil'
});

const errorMessage = ref('');
const isLoading = ref(false);
const showPassword = ref(false);
const tahunAjaranOptions = ref([]);
const toast = reactive({
  show: false,
  type: 'success',
  message: '',
  icon: 'check_circle'
});

const showToast = (type, icon, message) => {
  toast.type = type;
  toast.icon = icon;
  toast.message = message;
  toast.show = true;
  setTimeout(() => {
    toast.show = false;
  }, 2500);
};

const fetchTahunAjaran = async () => {
  try {
    const res = await axios.get('/api/tahun-ajarans');
    tahunAjaranOptions.value = res.data || [];
    if (tahunAjaranOptions.value.length > 0) {
      form.academicYear = tahunAjaranOptions.value[0].nama;
    }
  } catch {
    // ignore
  }
};

const handleLogin = async () => {
  errorMessage.value = '';
  isLoading.value = true;
  try {
    const res = await axios.post('/api/login', {
      email: form.email,
      password: form.password,
      academic_year: form.academicYear,
      semester: form.semester
    });
    if (res?.data?.csrf_token) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = res.data.csrf_token;
      const meta = document.querySelector('meta[name="csrf-token"]');
      if (meta) meta.setAttribute('content', res.data.csrf_token);
    }
    resetAuthCache();
    showToast('success', 'check_circle', 'Login berhasil. Mengalihkan...');
    setTimeout(() => {
      router.push('/admin/dashboard');
    }, 400);
  } catch (err) {
    errorMessage.value = err?.response?.data?.message || 'Login gagal.';
    showToast('error', 'error', 'Login gagal. Periksa kembali email atau password Anda.');
  } finally {
    isLoading.value = false;
  }
};

fetchTahunAjaran();
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap');

.login-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: var(--color-background-light);
  color: #1e293b;
  font-family: var(--font-family);
}

.login-main {
  width: 100%;
  max-width: 420px;
}

.login-card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
}

.login-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.logo-box {
  width: 64px;
  height: 64px;
  margin: 0 auto 1rem;
  background: #eff6ff;
  color: var(--color-primary);
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-icon {
  font-size: 2rem;
}

.login-title {
  font-family: "Manrope", sans-serif;
  font-size: 1.6rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  margin-bottom: 0.25rem;
}

.login-subtitle {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  font-weight: 600;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.field label {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-text-muted);
}

.field input,
.field select {
  height: 44px;
  border-radius: 0.75rem;
  border: 1px solid var(--color-border);
  padding: 0 0.85rem;
  font-size: 0.9rem;
  background: #ffffff;
  color: var(--color-text-main);
  outline: none;
  transition: all 0.2s ease;
}

.field input:focus,
.field select:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.12);
}

.password-wrap {
  position: relative;
}

.password-wrap input {
  width: 100%;
  padding-right: 2.5rem;
}

.toggle-btn {
  position: absolute;
  right: 0.65rem;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  color: #94a3b8;
  cursor: pointer;
}

.toggle-btn:hover {
  color: var(--color-primary);
}

.grid-two {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.actions {
  margin-top: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.submit-btn {
  height: 48px;
  border-radius: 0.75rem;
  background: var(--color-primary);
  color: #ffffff;
  font-weight: 800;
  font-size: 0.9rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.submit-btn:hover {
  background: var(--color-primary-dark);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.help-link {
  text-align: center;
}

.help-link a {
  color: var(--color-primary);
  font-size: 0.75rem;
  font-weight: 700;
  text-decoration: none;
}

.help-link a:hover {
  text-decoration: underline;
}

.login-footer {
  margin-top: 1.5rem;
  font-size: 0.65rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--color-text-muted);
  font-weight: 700;
}

.toast {
  position: fixed;
  top: 1.5rem;
  left: 50%;
  transform: translateX(-50%);
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.7rem 1.1rem;
  border-radius: 9999px;
  background: #ffffff;
  border: 1px solid var(--color-border);
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-main);
  z-index: 50;
  animation: toastIn 0.25s ease-out;
}
.toast.success { border-color: #dcfce7; }
.toast.success .material-symbols-outlined { color: #22c55e; }
.toast.error { border-color: #fee2e2; }
.toast.error .material-symbols-outlined { color: #ef4444; }
.toast-text { white-space: nowrap; }

@keyframes toastIn {
  from { opacity: 0; transform: translate(-50%, -8px); }
  to { opacity: 1; transform: translate(-50%, 0); }
}

@media (max-width: 520px) {
  .grid-two {
    grid-template-columns: 1fr;
  }
}
</style>
