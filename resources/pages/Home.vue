<template>
    <Head title="Inicio" />

    <Layout>
        <section
            class="rounded-2xl bg-gradient-to-br from-indigo-50 to-white p-8 ring-1 ring-gray-100"
        >
            <p class="text-sm uppercase tracking-widest text-indigo-600">
                Galería de arte
            </p>
            <h1 class="mt-2 text-4xl font-bold text-gray-800">
                🎨 Galería de Cuadros
            </h1>
            <p class="mt-2 max-w-2xl text-gray-600">
                Bienvenido a nuestra galería. Descubre obras, guarda tus favoritas
                y comparte tu opinión con otros amantes del arte.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <Link
                    href="/cuadros"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    Ver galería
                </Link>
                <Link
                    v-if="$page.props.auth?.user?.is_vendor || $page.props.auth?.user?.is_admin"
                    href="/cuadros/create"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50"
                >
                    Publicar obra
                </Link>
            </div>
        </section>

        <section v-if="featuredPaintings.length" class="mt-10">
            <h2 class="text-2xl font-bold text-gray-800">Últimas obras</h2>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Link
                    v-for="painting in featuredPaintings"
                    :key="painting.id"
                    :href="`/cuadros/${painting.id}`"
                    class="overflow-hidden rounded-xl bg-white shadow-sm hover:shadow-md"
                >
                    <div
                        class="flex h-32 items-center justify-center bg-gray-100 text-5xl"
                    >
                        <img
                            v-if="painting.image_url"
                            :src="painting.image_url"
                            :alt="painting.title"
                            class="h-full w-full object-cover"
                        />
                        <span v-else>{{ painting.emoji }}</span>
                    </div>
                    <div class="p-4">
                        <p class="font-medium text-gray-800">
                            {{ painting.title }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ painting.artist || "Anónimo" }}
                        </p>
                    </div>
                </Link>
            </div>
        </section>

        <section v-if="latestPublications.length" class="mt-10">
            <div class="flex items-end justify-between gap-4">
                <h2 class="text-2xl font-bold text-gray-800">
                    Últimas publicaciones
                </h2>
                <Link
                    href="/publicaciones"
                    class="text-sm text-blue-600 hover:underline"
                >
                    Ver todas
                </Link>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Link
                    v-for="publication in latestPublications"
                    :key="publication.id"
                    :href="`/publicaciones/${publication.id}`"
                    class="rounded-xl bg-white p-5 shadow-sm hover:shadow-md"
                >
                    <p
                        class="text-xs uppercase tracking-wide text-indigo-600"
                    >
                        {{ publication.category || "General" }}
                    </p>
                    <p class="mt-2 font-medium text-gray-800">
                        {{ publication.title }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ publication.author || "Anónimo" }}
                    </p>
                </Link>
            </div>
        </section>
    </Layout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    featuredPaintings: {
        type: Array,
        default: () => [],
    },
    latestPublications: {
        type: Array,
        default: () => [],
    },
});
</script>
