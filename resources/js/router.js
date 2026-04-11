import { createRouter, createWebHistory } from 'vue-router';
import Login from './Pages/Login.vue';
import MainLayout from './Layouts/MainLayout.vue';
import Dashboard from './Pages/Dashboard.vue';
import Lembaga from './Pages/Lembaga.vue';
import Teachers from './Pages/Teachers/Index.vue';
import Assignments from './Pages/Teachers/Assignments.vue';
import Classes from './Pages/Classes/Index.vue';
import Subjects from './Pages/Subjects/Index.vue';
import Students from './Pages/Students/Index.vue';
import Mutations from './Pages/Mutations/Index.vue';
import Alumni from './Pages/Alumni/Index.vue';
import Users from './Pages/Users/Index.vue';
import Grades from './Pages/Grades/Index.vue';
import Attendance from './Pages/Attendance/Index.vue';
import PrintBooks from './Pages/Prints/Index.vue';
import axios from 'axios';

const routes = [
    {
        path: '/',
        name: 'Login',
        component: Login,
    },
    {
        path: '/admin',
        component: MainLayout,
        children: [
            {
                path: 'dashboard',
                alias: '', // Makes /admin render dashboard
                name: 'Dashboard',
                component: Dashboard,
            },
            {
                path: 'lembaga',
                name: 'Lembaga',
                component: Lembaga,
            },
            {
                path: 'teachers',
                name: 'Teachers',
                component: Teachers,
            },
            {
                path: 'assignments',
                name: 'Assignments',
                component: Assignments,
            },
            {
                path: 'classes',
                name: 'Classes',
                component: Classes,
            },
            {
                path: 'subjects',
                name: 'Subjects',
                component: Subjects,
            },
            {
                path: 'students',
                name: 'Students',
                component: Students,
            },
            {
                path: 'grades',
                name: 'Grades',
                component: Grades,
            },
            {
                path: 'attendance',
                name: 'Attendance',
                component: Attendance,
            },
            {
                path: 'students/upgrade',
                name: 'NaikKelas',
                component: () => import('./Pages/Students/NaikKelas.vue'),
            },
            {
                path: 'mutations',
                name: 'Mutations',
                component: Mutations,
            },
            {
                path: 'alumni',
                name: 'Alumni',
                component: Alumni,
            },
            {
                path: 'users',
                name: 'Users',
                component: Users,
            },
            {
                path: 'print-books',
                name: 'PrintBooks',
                component: PrintBooks,
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

let authChecked = false;
let isAuthed = false;
let cachedUser = null;

const checkAuth = async () => {
    if (authChecked) return isAuthed;
    try {
        const res = await axios.get('/api/me');
        cachedUser = res.data?.user || null;
        isAuthed = true;
    } catch {
        isAuthed = false;
        cachedUser = null;
    }
    authChecked = true;
    return isAuthed;
};

export const resetAuthCache = () => {
    authChecked = false;
    isAuthed = false;
    cachedUser = null;
};

router.beforeEach(async (to, from, next) => {
    if (to.path.startsWith('/admin')) {
        const ok = await checkAuth();
        if (!ok) return next('/');
        if (to.path.startsWith('/admin/users')) {
            if (cachedUser?.role !== 'admin') return next('/admin/dashboard');
        }
    }
    if (to.path === '/') {
        const ok = await checkAuth();
        if (ok) return next('/admin/dashboard');
    }
    return next();
});

export default router;
