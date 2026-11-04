
import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Link, Head, router } from '@inertiajs/vue3';
import Layout from '../shared/Layout.vue';
import { applyTheme, resolveTheme } from './theme';

// Al iniciar sesión, cerrar sesión o navegar, aplicamos el tema del usuario
// que corresponde a la página actual. La base de datos manda sobre la caché.
router.on('navigate', (event) => {
    const user = event.detail.page.props.auth?.user;
    const userId = user?.id ?? null;

    applyTheme(resolveTheme(user?.theme, userId));
});

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

    title: title => `Galería de Arte - ${title}`,
});
