import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router';
import '../css/app.css';
import GpsMap from './components/GpsMap.vue';

const app = createApp(App);
app.use(router);
app.component('gps-map', GpsMap);
app.mount('#app');
