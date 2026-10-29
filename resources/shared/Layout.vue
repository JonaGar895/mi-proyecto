<template>
    <Head>
        <title>Galería de Arte</title>
        <meta
            name="description"
            content="Galería de arte en línea"
            head-key="description"
        />
    </Head>

    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm">
            <div
                class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center"
            >
                <h1 class="text-xl font-bold text-gray-800">
                    🎨 Galería de Arte
                </h1>

                <p class="text-sm ml-4 text-gray-600">
                    ¡Hola de nuevo, {{ userName }}!
                </p>

                <Nav />
            </div>
        </header>

        <div
            v-if="$page.props.flash?.success"
            class="max-w-7xl mx-auto px-6 pt-4"
        >
            <div class="rounded-lg bg-green-100 px-4 py-2 text-green-800">
                {{ $page.props.flash.success }}
            </div>
        </div>

        <div
            v-if="$page.props.flash?.error"
            class="max-w-7xl mx-auto px-6 pt-4"
        >
            <div class="rounded-lg bg-red-100 px-4 py-2 text-red-800">
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

export default {
    components: {
        Head,
        Nav,
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