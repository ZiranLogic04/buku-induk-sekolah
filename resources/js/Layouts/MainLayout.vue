<template>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside :class="['sidebar', { 'sidebar-closed': !sidebarOpen, 'sidebar-collapsed': !sidebarOpen }]">
      <!-- Sidebar Header -->
      <div class="sidebar-header">
        <div class="brand-row">
          <div class="logo-box">
            <span class="material-symbols-outlined logo-icon">school</span>
          </div>
          <span class="app-name">SIS Admin</span>
        </div>
        
        <!-- Mobile Close Button -->
        <button class="close-sidebar-btn lg-hidden" @click="sidebarOpen = false">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav no-scrollbar">
        <router-link to="/admin/dashboard" class="nav-item" active-class="active">
          <span class="material-symbols-outlined icon">dashboard</span>
          <span class="label">Dashboard</span>
        </router-link>

        <div class="nav-group">
            <!-- Lembaga is /admin/institution based on router likely -->
          <router-link to="/admin/lembaga" class="nav-item" active-class="active">
            <span class="material-symbols-outlined icon">account_balance</span>
            <span class="label">Lembaga</span>
          </router-link>

          <router-link to="/admin/classes" class="nav-item" active-class="active">
            <span class="material-symbols-outlined icon">class</span>
            <span class="label">Kelas</span>
          </router-link>

          <router-link to="/admin/subjects" class="nav-item" active-class="active">
            <span class="material-symbols-outlined icon">book</span>
            <span class="label">Mata Pelajaran</span>
          </router-link>

          <!-- Teachers Dropdown -->
          <div class="nav-dropdown">
            <button class="nav-item w-full justify-between" @click="toggleMenu('teachers')" :class="{ 'active': isTeachersActive }">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined icon">person</span>
                    <span class="label">Guru</span>
                </div>
                <span class="material-symbols-outlined icon-xs transition-transform" :class="{ 'rotate-180': isMenuOpen('teachers') }">expand_more</span>
            </button>
            <div class="nav-submenus" v-show="isMenuOpen('teachers')">
                 <router-link to="/admin/teachers" class="nav-subitem" active-class="active">Data Guru</router-link>
                 <router-link to="/admin/assignments" class="nav-subitem" active-class="active">Pengajaran</router-link>
            </div>
          </div>
          <!-- Students Dropdown -->
          <div class="nav-dropdown">
            <button class="nav-item w-full justify-between" @click="toggleMenu('students')" :class="{ 'active': isStudentsActive }">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined icon">group</span>
                    <span class="label">Siswa</span>
                </div>
                <span class="material-symbols-outlined icon-xs transition-transform" :class="{ 'rotate-180': isMenuOpen('students') }">expand_more</span>
            </button>
            <div class="nav-submenus" v-show="isMenuOpen('students')">
                 <router-link to="/admin/students" class="nav-subitem" active-class="active">Data Siswa</router-link>
                 <router-link to="/admin/students/upgrade" class="nav-subitem" active-class="active">Naik Kelas</router-link>
                 <router-link to="/admin/mutations" class="nav-subitem" active-class="active">Mutasi</router-link>
                 <router-link to="/admin/alumni" class="nav-subitem" active-class="active">Alumni</router-link>
                 <router-link to="/admin/print-books" class="nav-subitem" active-class="active">Cetak Buku Induk</router-link>
            </div>
          </div>

          <router-link to="/admin/grades" class="nav-item" active-class="active">
            <span class="material-symbols-outlined icon">grading</span>
            <span class="label">Nilai</span>
          </router-link>

          <router-link to="/admin/attendance" class="nav-item" active-class="active">
            <span class="material-symbols-outlined icon">fact_check</span>
            <span class="label">Absen</span>
          </router-link>
        </div>

      <div class="nav-group" v-if="isAdmin">
          <router-link to="/admin/users" class="nav-item" active-class="active">
            <span class="material-symbols-outlined icon">manage_accounts</span>
            <span class="label">Users</span>
          </router-link>
        </div>


      </nav>

      <!-- Sidebar Footer (removed per request) -->
    </aside>

    <!-- Main Content Wrapper -->
    <main class="main-content">
      <!-- Top Header -->
      <header class="top-header">
        <div class="header-left">
          <!-- Sidebar Toggle -->
          <button class="menu-toggle-btn" @click="sidebarOpen = !sidebarOpen">
            <span class="material-symbols-outlined">menu</span>
          </button>
          
          <!-- Breadcrumb removed per request -->
        </div>

        <div class="header-right">
          <div v-if="contextLabel" class="context-pill">
            <span class="material-symbols-outlined context-icon">calendar_month</span>
            <span class="context-text">{{ contextLabel }}</span>
          </div>
          <button class="logout-btn header" title="Logout" @click="handleLogout">
            <span class="material-symbols-outlined">logout</span>
            Keluar
          </button>
        </div>
      </header>

      <!-- Scrollable Content Area -->
      <div class="content-scrollable no-scrollbar">
         <div class="content-container">
            <!-- Page Content Injected Here -->
            <router-view></router-view>
            
            <!-- Footer -->
            <footer class="main-footer">
                <div class="footer-divider"></div>
                <p class="footer-text">© 2025 School Information System. v3.0.0</p>
            </footer>
         </div>
      </div>
    </main>
    
    <!-- Overlay for mobile sidebar -->
    <div v-if="sidebarOpen" class="sidebar-overlay lg-hidden" @click="sidebarOpen = false"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useRouter } from 'vue-router';
import axios from 'axios';

const sidebarOpen = ref(true);
const route = useRoute();
const router = useRouter();
const openMenus = ref([]);
const isAdmin = ref(false);
const contextLabel = ref('');

// Auto open menu based on current route
onMounted(() => {
    if (route.path.includes('/admin/teachers') || route.path.includes('/admin/assignments')) {
        openMenus.value.push('teachers');
    }
    if (route.path.includes('/admin/students') || route.path.includes('/admin/mutations') || route.path.includes('/admin/alumni') || route.path.includes('/admin/print-books')) {
        openMenus.value.push('students');
    }

    axios.get('/api/me').then((res) => {
        isAdmin.value = res.data?.user?.role === 'admin';
        const ctx = res.data?.context;
        if (ctx?.academic_year && ctx?.semester) {
            contextLabel.value = `${ctx.academic_year} • ${ctx.semester}`;
        }
    }).catch(() => {
        isAdmin.value = false;
    });
});

const toggleMenu = (menu) => {
    if (!sidebarOpen.value) return;
    const index = openMenus.value.indexOf(menu);
    if (index > -1) {
        openMenus.value.splice(index, 1);
    } else {
        openMenus.value.push(menu);
    }
};


const isMenuOpen = (menu) => openMenus.value.includes(menu);

const isTeachersActive = computed(() => {
    return route.path.startsWith('/admin/teachers') || route.path.startsWith('/admin/assignments');
});

const isStudentsActive = computed(() => {
    return route.path.startsWith('/admin/students') || route.path.startsWith('/admin/mutations') || route.path.startsWith('/admin/alumni') || route.path.startsWith('/admin/print-books');
});

const handleLogout = async () => {
    try {
        await axios.post('/api/logout');
    } catch (e) {
        // ignore
    } finally {
        router.push('/');
        window.location.reload();
    }
};
</script>

<style scoped>
.app-layout {
  display: flex;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  background-color: var(--color-background-light);
  font-family: var(--font-family);
  color: var(--color-text-main);
}

/* Utilities */
.lg-hidden { display: flex; }
.lg-visible { display: none; }
.md-visible { display: none; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

@media (min-width: 768px) {
    .md-visible { display: block; }
}
@media (min-width: 1024px) {
    .lg-hidden { display: none; }
    .lg-visible { display: flex; }
}

/* Sidebar */
.sidebar {
  width: 280px;
  background-color: white;
  border-right: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  z-index: 30;
  transition: transform 0.45s ease, width 0.45s ease;
  transform: translateX(-100%); /* Hidden on mobile by default */
}

.sidebar.sidebar-collapsed {
    width: 96px;
}

@media (min-width: 1024px) {
    .sidebar {
        position: static;
        transform: translateX(0);
        flex-shrink: 0;
    }
}

/* Mobile Sidebar Open Override */
@media (max-width: 1023px) {
    .sidebar.sidebar-closed {
        transform: translateX(-100%);
    }
}


/* Sidebar Header */
.sidebar-header {
    height: 80px; /* h-20 */
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.brand-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.logo-box {
    width: 2.5rem;
    height: 2.5rem;
    background-color: var(--color-primary);
    border-radius: 0.75rem; /* rounded-xl */
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.2);
}

.logo-icon {
    color: white;
    font-size: 1.5rem;
}

.app-name {
    font-size: 1.25rem; /* xl */
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.025em;
    white-space: nowrap;
}

/* Sidebar Nav */
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}


.nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: var(--sidebar-text);
    text-decoration: none;
    border-radius: 9999px;
    font-weight: 500;
    transition: all 0.2s;
}

.sidebar.sidebar-collapsed .app-name {
    display: none;
}

.sidebar.sidebar-collapsed .sidebar-header {
    justify-content: center;
    padding: 0;
}

.sidebar.sidebar-collapsed .brand-row {
    justify-content: center;
    width: 100%;
}

.sidebar.sidebar-collapsed .sidebar-nav {
    padding: 0.75rem 0.5rem;
}

.sidebar.sidebar-collapsed .nav-item .label,
.sidebar.sidebar-collapsed .nav-subitem,
.sidebar.sidebar-collapsed .section-label,
.sidebar.sidebar-collapsed .section-line,
.sidebar.sidebar-collapsed .nav-submenus {
    display: none;
}

.sidebar.sidebar-collapsed .nav-item {
    justify-content: center;
    padding: 0.75rem 0.5rem;
}

.sidebar.sidebar-collapsed .logo-box {
    margin-right: 0;
}

.nav-item:hover {
    background-color: #f8fafc;
}

.nav-item.active {
    background-color: #1e40af;
    color: white !important;
    box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.2), 0 2px 4px -1px rgba(30, 64, 175, 0.1);
}

.nav-item.active .icon {
    color: white !important;
}


.nav-item .icon {
    font-size: 1.25rem;
}

/* Nav Dropdown */
.nav-dropdown {
    display: flex;
    flex-direction: column;
    position: relative;
}

.nav-item.w-full {
    width: 100%;
    border: none;
    background: transparent;
    cursor: pointer;
    font-family: inherit;
    font-size: inherit;
}

.nav-item.w-full.active {
    background-color: #1e40af;
    color: white !important;
    box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.2), 0 2px 4px -1px rgba(30, 64, 175, 0.1);
}

.nav-item.w-full.active .icon {
    color: white !important;
}

.justify-between { justify-content: space-between; }
.flex { display: flex; }
.items-center { align-items: center; }
.gap-3 { gap: 0.75rem; }

.icon-xs { font-size: 1.125rem; }
.transition-transform { transition: transform 0.2s; }
.rotate-180 { transform: rotate(180deg); }

.nav-submenus {
    padding-left: 1rem;
    margin-top: 0.25rem;
    border-left: 2px solid #f1f5f9; /* border-l-2 border-slate-100 */
    margin-left: 1.5rem; /* ml-6 approx to align with icon */
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.sidebar.sidebar-collapsed .nav-submenus {
    display: none !important;
}

.nav-subitem {
    display: block;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--sidebar-text);
    text-decoration: none;
    border-radius: 0 9999px 9999px 0;
    transition: all 0.2s;
}

.nav-subitem:hover {
    color: var(--color-primary);
    background-color: #f8fafc;
}

.nav-subitem.active {
    color: var(--color-primary);
    font-weight: 700;
    background-color: rgba(30, 64, 175, 0.08);
}

/* Nav Section Divider */
.nav-section {
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-label {
    font-size: 0.625rem; /* 10px */
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    white-space: nowrap;
}

.section-line {
    height: 1px;
    flex: 1;
    background-color: #f1f5f9;
}

.nav-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

/* Sidebar Footer */
.logout-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #ef4444;
    background: #fff5f5;
    border: 1px solid #fee2e2;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0.5rem 0.75rem;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.logout-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}

.logout-btn.full {
    width: 100%;
    justify-content: center;
}

.logout-btn.header {
    background: #fff5f5;
    border: 1px solid #fee2e2;
}

/* Main Content */
.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    position: relative;
    background-color: #f8fafc; /* bg-slate-50/50 approx */
}

/* Top Header */
.top-header {
    height: 64px;
    background-color: white;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    flex-shrink: 0;
    z-index: 10;
    box-shadow: 0 6px 16px -12px rgba(15, 23, 42, 0.2);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.menu-toggle-btn {
    color: #64748b;
    background: none;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #94a3b8;
}

.breadcrumb-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: #475569;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.context-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    background: #f8fafc;
    border: 1px solid var(--color-border);
    color: #475569;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}
.context-icon { font-size: 1rem; color: #64748b; }
.context-text { font-weight: 800; }

/* Search */
.search-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 1.125rem;
}

.search-input {
    width: 16rem; /* w-64 */
    padding: 0.375rem 1rem 0.375rem 2.5rem; /* py-1.5 */
    background-color: #f8fafc;
    border: none;
    border-radius: 9999px;
    font-size: 0.875rem;
    color: #334155;
    outline: none;
}

.search-input:focus {
    box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.2);
}

.notification-btn {
    position: relative;
    padding: 0.5rem;
    color: #64748b;
    background: none;
    border: none;
    border-radius: 9999px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-btn:hover {
    background-color: #f8fafc;
    color: var(--color-primary);
}

.notification-dot {
    position: absolute;
    top: 0.625rem;
    right: 0.625rem;
    width: 0.5rem;
    height: 0.5rem;
    background-color: #ef4444;
    border-radius: 50%;
    border: 2px solid white;
}

.vertical-divider {
    height: 2rem;
    width: 1px;
    background-color: #e2e8f0;
    margin: 0 0.25rem;
}

.profile-dropdown {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.5rem;
    border-radius: 9999px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.profile-dropdown:hover {
    background-color: #f8fafc;
}

.profile-img {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    border: 2px solid rgba(30, 64, 175, 0.2);
    object-fit: cover;
}

.dropdown-icon {
    color: #94a3b8;
    font-size: 1.125rem;
}

/* Content Area */
.content-scrollable {
    flex: 1;
    overflow-y: auto;
    background-color: rgba(248, 250, 252, 0.5); /* match bg-slate-50/50 */
}

.content-container {
     /* max-w-[1400px] mx-auto p-6 md:p-8 handled in page components mostly, 
        but we can add a wrapper if needed. 
        The provided design puts the max-w constrained IN the layout or page?
        Snippet has <div class="max-w-[1400px] mx-auto p-6 md:p-8"> IN the main tag.
     */
     /* We will let page components handle their internal padding/max-width to fit the "grid" system 
        OR we can put it here.
        Institution.vue ALREADY has max-width and padding.
        So this container just holds it.
     */
     min-height: 100%;
     display: flex;
     flex-direction: column;
}

.main-footer {
    margin-top: auto;
    padding-bottom: 2rem;
    text-align: center;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.footer-divider {
    height: 1px;
    width: 100%;
    background-color: #e2e8f0;
    margin-bottom: 1.5rem;
}

.footer-text {
    font-size: 0.625rem;
    color: #94a3b8;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.2em;
}

/* Overlay */
.sidebar-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 20;
}
</style>

