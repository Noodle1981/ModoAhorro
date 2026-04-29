import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'ModoAhorro';

const el = document.getElementById('app');
console.log('Mount element:', el);
if (el) {
    console.log('Page data:', el.dataset.page);
}

if (el) {
    createInertiaApp({
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => {
            console.log('Resolving page:', name);
            return resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));
        },
        setup({ el, App, props, plugin }) {
            console.log('Setting up app on:', el);
            return createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue)
                .mount(el);
        },
        progress: {
            color: '#4B5563',
        },
    });
} else {
    console.warn('Inertia mount element "#app" not found. Skipping createInertiaApp.');
}
