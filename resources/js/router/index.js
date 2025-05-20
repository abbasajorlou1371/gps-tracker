import { createRouter, createWebHistory } from 'vue-router';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';
import Home from '../components/Home.vue';
import GpsMap from '../components/GpsMap.vue';

// Configure NProgress
NProgress.configure({
    showSpinner: false,
    easing: 'ease',
    speed: 500,
    trickleSpeed: 200,
    minimum: 0.2
});

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home
    },
    {
        path: '/gps-tracking',
        name: 'gps-tracking',
        component: GpsMap
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Add navigation guards for progress bar
router.beforeEach((to, from, next) => {
    NProgress.start();
    next();
});

router.afterEach(() => {
    setTimeout(() => {
        NProgress.done(true);
    }, 100);
});

export default router;
