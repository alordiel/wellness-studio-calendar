import { createApp } from 'vue';
import App from './App.vue';
import './assets/tailwind.css'; // Import Tailwind CSS
import router from './router';
// Create app and mount to specific element
const app = createApp(App);
app.use(router);
app.mount('#app-dashboard'); // Mount to your specific element