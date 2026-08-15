import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => {
        if (!title || title.trim() === '' || title.toLowerCase() === 'icha' || title.toLowerCase() === 'home' || title.toLowerCase() === 'icha conference portal') {
            return 'ICHA';
        }
        if (title.toLowerCase().includes('dashboard') || title.toLowerCase() === 'workspace') {
            return 'ICHA | Dashboard';
        }
        if (title.startsWith('ICHA |')) {
            return title;
        }
        const cleanTitle = title.replace(/\s*-\s*ICHA(\s*\d{4})?/i, '').replace(/\s*-\s*Admin/i, '').trim();
        return `ICHA | ${cleanTitle}`;
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
