import { createApp } from 'vue';
import { createPinia } from 'pinia';
import Checkout from '@/Pages/pos/Checkout.vue';

const app = createApp(Checkout);
const pinia = createPinia();
app.use(pinia);

app.mount('#app');
