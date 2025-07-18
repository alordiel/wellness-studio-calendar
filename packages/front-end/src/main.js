
import {createApp} from 'vue';
import App from './App.vue';
import { createPinia } from 'pinia'
document.addEventListener('DOMContentLoaded', function() {
    const app = createApp(App);
    app.use(createPinia());
    app.mount('#wellness-calendar-app');
});