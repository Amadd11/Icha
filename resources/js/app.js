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
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // v-reveal directive for easy declarative scroll animation
        app.directive('reveal', {
            mounted(el, binding) {
                el.classList.add('fade-in');
                if (binding.value && typeof binding.value === 'string') {
                    el.classList.add(binding.value);
                }
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
                observer.observe(el);
            }
        });

        return app.mount(el);
    },
    progress: {
        color: '#FACE68',
    },
});

// Auto-observe all static .fade-in elements on page load & Inertia transitions
if (typeof window !== 'undefined' && 'IntersectionObserver' in window) {
    const globalObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                globalObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    const scanAndObserve = () => {
        document.querySelectorAll('.fade-in:not(.visible)').forEach((el) => {
            globalObserver.observe(el);
        });
    };

    window.addEventListener('DOMContentLoaded', scanAndObserve);
    document.addEventListener('inertia:finish', () => setTimeout(scanAndObserve, 80));
}

// Security: Prevent accessing protected views via browser Back button after logout
if (typeof window !== 'undefined') {
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            window.location.reload();
        }
    });
}
