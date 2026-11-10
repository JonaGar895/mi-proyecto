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
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 sm:py-4 lg:px-8 2xl:max-w-[1500px]"
            >
                <div class="flex items-center gap-2 sm:gap-3">
                    <h1 class="text-lg font-bold text-gray-800 dark:text-gray-100 sm:text-xl">
                        🎨 Galería de Arte
                    </h1>

                    <NotificationBell />
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 sm:gap-3">
                    <Nav />

                    <div class="hidden xl:block">
                        <ThemeToggle />
                    </div>
                </div>
            </div>
        </header>

        <div
            v-if="$page.props.flash?.success"
            class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8 2xl:max-w-[1500px]"
        >
            <div class="rounded-lg bg-green-100 px-4 py-2 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                {{ $page.props.flash.success }}
            </div>
        </div>

        <div
            v-if="$page.props.flash?.error"
            class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8 2xl:max-w-[1500px]"
        >
            <div class="rounded-lg bg-red-100 px-4 py-2 text-red-800 dark:bg-red-900/40 dark:text-red-200">
                {{ $page.props.flash.error }}
            </div>
        </div>

        <main class="mx-auto w-full max-w-7xl p-4 sm:p-6 lg:p-8 2xl:max-w-[1500px]">
            <slot />
        </main>
    </div>
</template>

<script>
import { Head } from "@inertiajs/vue3";
import Nav from "./Nav.vue";
import NotificationBell from "./NotificationBell.vue";
import ThemeToggle from "./ThemeToggle.vue";

export default {
    components: {
        Head,
        Nav,
        NotificationBell,
        ThemeToggle,
    },
};
</script>