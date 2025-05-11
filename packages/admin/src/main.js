import {createApp} from 'vue';
import App from './App.vue';
import './assets/tailwind.css';

import router from './router';

import { createPinia } from 'pinia'

import {createVuetify} from "vuetify"
import 'vuetify/styles';
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'
import {aliases, mdi} from 'vuetify/iconsets/mdi'

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: {
            mdi,
        },
    },
});
const pinia = createPinia();

const app = createApp(App);

app.use(pinia);
app.use(router);
app.use(vuetify);

app.mount('#app-dashboard');