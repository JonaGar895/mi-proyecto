<template>
    <Head>
        <title>Galería de Arte</title>
        <meta
            name="description"
            content="Galería de arte en línea"
            head-key="description"
        />
    </Head>

    <div class="min-h-screen bg-gray-50 transition-colors duration-300 dark:bg-gray-950">
        <header class="bg-white shadow-sm transition-colors duration-300 dark:border-b dark:border-gray-800 dark:bg-gray-900 dark:shadow-none">
            <div
                class="max-w-7xl mx-auto px-6 py-4 flex flex-wrap justify-between items-center gap-4"
            >
                <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                    🎨 Galería de Arte
                </h1>

                <div class="flex flex-wrap items-center gap-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        ¡Hola de nuevo, {{ userName }}!
                    </p>

                    <Nav />

                    <ThemeToggle />
                </div>
            </div>
        </header>

        <div
            v-if="$page.props.flash?.success"
            class="max-w-7xl mx-auto px-6 pt-4"
        >
            <div class="rounded-lg bg-green-100 px-4 py-2 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                {{ $page.props.flash.success }}
            </div>
        </div>

        <div
            v-if="$page.props.flash?.error"
            class="max-w-7xl mx-auto px-6 pt-4"
        >
            <div class="rounded-lg bg-red-100 px-4 py-2 text-red-800 dark:bg-red-900/40 dark:text-red-200">
                {{ $page.props.flash.error }}
            </div>
        </div>

        <main class="max-w-7xl mx-auto p-6">
            <slot />
        </main>
    </div>
</template>

<script>
import { Head } from "@inertiajs/vue3";
import Nav from "./Nav.vue";
import ThemeToggle from "./ThemeToggle.vue";

export default {
    components: {
        Head,
        Nav,
        ThemeToggle,
    },
    computed: {
        userName() {
            return (
                this.$page.props.auth?.user?.name ??
                this.$page.props.auth?.user?.username ??
                this.$page.props.auth?.user?.email ??
                "Usuario"
            );
        },
    },
};
</script>