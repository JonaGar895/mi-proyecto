
import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Link, Head } from '@inertiajs/vue3';
import Layout from '../shared/Layout.vue';

createInertiaApp({
    resolve: async (name) => {
        const pages = import.meta.glob('../pages/**/*.vue');

        const path = `../pages/${name}.vue`;

        if (!pages[path]) {
            throw new Error(`No se encuentra la página: ${path}`);
        }

        return pages[path]();
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
            .component('Layout', Layout)
            .mount(el);
    },

    title: title => `My App - ${title}`,
});
