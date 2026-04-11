<template>
    <div class="dashboard-page">
        <!-- Dashboard Header -->
        <div class="header-card">
            <h1 class="page-title">Dashboard Akademik</h1>
            <p class="page-subtitle">Pantau aktivitas terbaru dan ringkasan data lembaga secara real-time.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <!-- Total Guru -->
            <div class="stat-card">
                <div class="stat-icon-box bg-blue">
                    <span class="material-symbols-outlined icon-lg">person</span>
                </div>
                <div>
                    <p class="stat-label">Total Guru</p>
                    <p class="stat-value">{{ stats.teachers_count }}</p>
                </div>
            </div>
            
            <!-- Total Siswa -->
            <div class="stat-card">
                <div class="stat-icon-box bg-emerald">
                    <span class="material-symbols-outlined icon-lg">group</span>
                </div>
                <div>
                    <p class="stat-label">Total Siswa</p>
                    <p class="stat-value">{{ stats.students_count }}</p>
                </div>
            </div>

            <!-- Total Kelas -->
            <div class="stat-card">
                <div class="stat-icon-box bg-amber">
                    <span class="material-symbols-outlined icon-lg">class</span>
                </div>
                <div>
                    <p class="stat-label">Total Kelas</p>
                    <p class="stat-value">{{ stats.classes_count }}</p>
                </div>
            </div>

            <!-- Tahun Ajaran -->
            <div class="stat-card">
                <div class="stat-icon-box bg-purple">
                    <span class="material-symbols-outlined icon-lg">calendar_today</span>
                </div>
                <div>
                    <p class="stat-label">Tahun Ajaran</p>
                    <p class="stat-value">{{ stats.academic_year }}</p>
                </div>
            </div>
        </div>

        <!-- Main Grid: Activities & Info -->
        <div class="main-grid">
            <!-- Recent Activity -->
            <div class="col-main">
                <div class="card">
                    <div class="card-header flex-between">
                        <div class="flex-center gap-3">
                             <div class="icon-box-primary">
                                <span class="material-symbols-outlined">history</span>
                            </div>
                            <h2 class="card-title">Aktivitas Terbaru</h2>
                        </div>
                    </div>
                    
                    <div class="card-body">
                         <div class="timeline" v-if="stats.recent_activities.length">
                            <div class="timeline-item" v-for="(act, idx) in stats.recent_activities" :key="idx">
                                <div class="timeline-icon" :class="getActivityClass(act.type)">
                                    <span class="material-symbols-outlined icon-xl">{{ getActivityIcon(act.type) }}</span>
                                </div>
                                <div class="timeline-content">
                                    <div class="flex-between mb-1">
                                        <h3 class="activity-title">{{ act.title }}</h3>
                                        <span class="activity-time">{{ act.time }}</span>
                                    </div>
                                    <p class="activity-desc">{{ act.desc }}</p>
                                </div>
                            </div>
                         </div>
                         <div v-else class="empty-activities">
                            Belum ada aktivitas terbaru.
                         </div>
                    </div>
                </div>
            </div>

            <!-- School Info -->
            <div class="col-side">
                <div class="card card-sticky">
                    <div class="card-header">
                        <h2 class="card-title">Informasi Lembaga</h2>
                    </div>
                    <div class="card-body flex-col gap-6">
                        <div class="school-profile">
                            <div class="school-logo">
                                <span class="material-symbols-outlined logo-icon">school</span>
                            </div>
                            <h3 class="school-name">{{ stats.institution?.nama || 'Nama Sekolah' }}</h3>
                            <p class="accreditation">Akreditasi {{ stats.institution?.status_akreditasi || 'A' }}</p>
                        </div>
                        
                        <div class="info-list">
                            <div class="info-item">
                                <span class="info-label">Kepala Sekolah</span>
                                <span class="info-value">{{ stats.institution?.nama_kepala_sekolah || '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status Sekolah</span>
                                <span class="info-value">{{ stats.institution?.status || '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Jenjang</span>
                                <span class="info-value">{{ stats.institution?.jenjang || '-' }}</span>
                            </div>
                        </div>

                         <router-link to="/admin/lembaga" class="btn-settings">
                            <span class="material-symbols-outlined icon-sm">settings</span>
                            Pengaturan Lembaga
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue';
import axios from 'axios';

const stats = reactive({
    teachers_count: 0,
    students_count: 0,
    classes_count: 0,
    academic_year: '-',
    institution: null,
    recent_activities: []
});

const fetchStats = async () => {
    try {
        const res = await axios.get('/api/admin/dashboard/stats');
        Object.assign(stats, res.data);
    } catch (e) {
        console.error("Gagal memuat statistik dashboard", e);
    }
};

const getActivityIcon = (type) => {
    if (type === 'student') return 'group_add';
    if (type === 'teacher') return 'person_add';
    if (type === 'grade') return 'grading';
    return 'history';
};

const getActivityClass = (type) => {
    if (type === 'student') return 'bg-emerald-solid';
    if (type === 'teacher') return 'bg-blue-solid';
    if (type === 'grade') return 'bg-amber-solid';
    return 'bg-blue-solid';
};

onMounted(() => {
    fetchStats();
});
</script>

<style scoped>
.dashboard-page { padding: 1rem; display: flex; flex-direction: column; gap: 1.5rem; }
@media (min-width: 768px) { .dashboard-page { padding: 2rem; } }
.header-card { margin-bottom: 0.5rem; }
.page-title { font-size: 1.875rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em; }
.page-subtitle { color: #64748b; margin-top: 0.25rem; font-size: 0.95rem; }
.stats-grid { display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem; }
@media (min-width: 640px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
.stat-card { background: #fff; padding: 1.5rem; border-radius: 1.25rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
.stat-icon-box { display: flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: 1rem; }
.bg-blue { background: #eff6ff; color: #2563eb; }
.bg-emerald { background: #ecfdf5; color: #059669; }
.bg-amber { background: #fffbeb; color: #d97706; }
.bg-purple { background: #faf5ff; color: #7c3aed; }
.icon-lg { font-size: 2rem; }
.stat-label { font-size: 0.875rem; font-weight: 600; color: #64748b; }
.stat-value { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.main-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
@media (min-width: 1024px) { .main-grid { grid-template-columns: 1.8fr 1fr; } }
.card { background: #fff; border-radius: 1.25rem; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.card-sticky { position: sticky; top: 2rem; }
.card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
.card-title { font-size: 1.1rem; font-weight: 800; color: #0f172a; }
.icon-box-primary { width: 2.5rem; height: 2.5rem; background: #f1f5f9; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: #1e40af; }
.flex-between { display: flex; align-items: center; justify-content: space-between; }
.flex-center { display: flex; align-items: center; }
.flex-col { display: flex; flex-direction: column; }
.gap-3 { gap: 0.75rem; }
.gap-6 { gap: 1.5rem; }
.mb-1 { margin-bottom: 0.25rem; }
.text-link { color: #2563eb; font-weight: 700; font-size: 0.875rem; background: none; border: none; cursor: pointer; }
.card-body { padding: 1.5rem; }
.timeline { display: flex; flex-direction: column; gap: 1.25rem; }
.timeline-item { display: flex; gap: 1rem; position: relative; }
.timeline-item:not(:last-child)::after { content: ''; position: absolute; left: 1.25rem; top: 2.5rem; bottom: -1rem; width: 2px; background: #f1f5f9; }
.timeline-icon { width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; z-index: 10; flex-shrink: 0; }
.bg-blue-solid { background: #1e40af; color: #fff; }
.bg-emerald-solid { background: #059669; color: #fff; }
.bg-amber-solid { background: #d97706; color: #fff; }
.icon-xl { font-size: 1.25rem; }
.activity-title { font-size: 0.95rem; font-weight: 700; color: #1e293b; }
.activity-time { font-size: 0.75rem; color: #94a3b8; font-weight: 600; }
.activity-desc { font-size: 0.875rem; color: #64748b; line-height: 1.5; }
.school-profile { text-align: center; }
.school-logo { width: 5rem; height: 5rem; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 1.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: #1e40af; }
.logo-icon { font-size: 3rem; }
.school-name { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem; }
.accreditation { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #059669; background: #ecfdf5; padding: 0.25rem 0.75rem; border-radius: 100px; display: inline-block; }
.info-list { display: flex; flex-direction: column; gap: 0.75rem; }
.info-item { display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9; }
.info-label { font-size: 0.875rem; color: #64748b; font-weight: 600; }
.info-value { font-size: 0.875rem; color: #0f172a; font-weight: 700; text-align: right; }
.btn-settings { display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%; padding: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 700; color: #475569; text-decoration: none; cursor: pointer; transition: all 0.2s; }
.btn-settings:hover { background: #f1f5f9; color: #1e293b; }
.empty-activities { text-align: center; padding: 2rem; color: #94a3b8; font-style: italic; }
</style>
