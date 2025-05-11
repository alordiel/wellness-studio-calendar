import {createApp} from 'vue';
import App from './App.vue';
import 'vuetify/styles'
import './assets/tailwind.css'; // Import Tailwind CSS
import router from './router';
import {createVuetify} from "vuetify"
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'


const vuetify = createVuetify({
    components,
    directives,
})
// Create app and mount to specific element
const app = createApp(App);
app.use(router);
app.use(vuetify);
app.mount('#app-dashboard'); // Mount to your specific element