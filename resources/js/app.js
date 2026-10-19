
import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Link, Head } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import Layout from '../shared/Layout.vue';

createInertiaApp({
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `../pages/${name}.vue`,
            import.meta.glob('../pages/**/*.vue')
        );

        page.default.layout ??= Layout;

        return page;
    },

    progress: {
        delay: 0,
        color: '#0060f0',
        includeCSS: true,
        showSpinner: true,
    },

    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .component('Link', Link)
            .component('Head', Head)
            .mount(el);
    },

    title: title => `My App - ${title}`,
});
