<template>
    <Head>
        <title>My App</title>
        <meta
            name="description"
            content="Home informacion"
            head-key="description"
        />
    </Head>

    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm">
            <div
                class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center"
            >
                <h1 class="text-xl font-bold text-gray-800">
                    🎨 My App
                </h1>

                <p class="text-sm ml-4 text-gray-600">
                    Welcome Back, {{ username }}!
                </p>

                <Nav />
            </div>
        </header>

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
        username() {
            return this.$page.props.auth?.user?.username ?? "Usuario";
        },
    },
};
</script>