<template>
    <Head title="Ajustes" />

    <Layout>
        <h1 class="text-4xl font-bold text-gray-800">⚙️ Ajustes</h1>
        <p class="mt-2 text-gray-600">
            Hola, {{ $page.props.auth.user.username }}
            <span class="text-sm text-gray-400">
                ({{ $page.props.auth.user.email }})
            </span>
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">
                    Obras publicadas
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800">
                    {{ stats.paintings }}
                </p>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">
                    Favoritos
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800">
                    {{ stats.favorites }}
                </p>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">
                    Comentarios
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800">
                    {{ stats.comments }}
                </p>
            </div>
        </div>

        <section class="mt-10">
            <h2 class="text-2xl font-bold text-gray-800">Mis favoritos</h2>

            <div
                v-if="favoritePaintings.length"
                class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="painting in favoritePaintings"
                    :key="painting.id"
                    :href="`/cuadros/${painting.id}`"
                    class="flex items-center gap-4 rounded-xl bg-white p-4 shadow-sm hover:shadow-md"
                >
                    <span class="text-4xl">{{ painting.emoji }}</span>
                    <span>
                        <span class="block font-medium text-gray-800">
                            {{ painting.title }}
                        </span>
                        <span class="block text-sm text-gray-500">
                            {{ painting.artist || "Anónimo" }}
                        </span>
                    </span>
                </Link>
            </div>

            <p v-else class="mt-4 text-gray-500">
                Todavía no has guardado ninguna obra. Explora la
                <Link href="/cuadros" class="text-blue-600 hover:underline">
                    galería
                </Link>.
            </p>
        </section>
    </Layout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    stats: {
        type: Object,
        default: () => ({ paintings: 0, favorites: 0, comments: 0 }),
    },
    favoritePaintings: {
        type: Array,
        default: () => [],
    },
});
</script>
