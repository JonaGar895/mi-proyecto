<template>
    <Head title="Inicio" />

    <Layout>
        <section
            class="rounded-2xl bg-gradient-to-br from-indigo-50 to-white p-5 ring-1 ring-gray-100 sm:p-8 lg:p-10"
        >
            <p class="text-sm uppercase tracking-widest text-indigo-600">
                Galería de arte
            </p>
            <h1 class="mt-2 text-3xl font-bold text-gray-800 sm:text-4xl">
                🎨 Galería de Cuadros
            </h1>
            <p class="mt-2 max-w-2xl text-gray-600">
                Bienvenido a nuestra galería. Descubre obras, guarda tus favoritas
                y comparte tu opinión con otros amantes del arte.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <Link
                    href="/cuadros"
                    class="w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700 sm:w-auto"
                >
                    Ver galería
                </Link>
                <Link
                    v-if="$page.props.auth?.user?.is_vendor || $page.props.auth?.user?.is_admin"
                    href="/cuadros/create"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-gray-700 hover:bg-gray-50 sm:w-auto"
                >
                    Publicar obra
                </Link>
            </div>
        </section>

        <section v-if="featuredPaintings.length" class="mt-8 sm:mt-10">
            <h2 class="text-xl font-bold text-gray-800 sm:text-2xl">Últimas obras</h2>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
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

        <section
            v-if="topRatedPainting"
            class="mt-8 rounded-2xl bg-gradient-to-br from-amber-50 to-white p-5 ring-1 ring-amber-100 sm:mt-10 sm:p-8"
        >
            <p class="text-sm uppercase tracking-widest text-amber-600">
                Obra mejor valorada
            </p>

            <div class="mt-4 flex flex-col gap-6 sm:flex-row sm:items-center">
                <Link
                    :href="`/cuadros/${topRatedPainting.id}`"
                    class="shrink-0 overflow-hidden rounded-xl bg-white shadow-sm hover:shadow-md"
                >
                    <div
                        class="flex h-40 items-center justify-center bg-gray-100 text-6xl sm:h-48 sm:w-72"
                    >
                        <img
                            v-if="topRatedPainting.image_url"
                            :src="topRatedPainting.image_url"
                            :alt="topRatedPainting.title"
                            class="h-full w-full object-cover"
                        />
                        <span v-else>{{ topRatedPainting.emoji }}</span>
                    </div>
                </Link>

                <div class="min-w-0">
                    <h2 class="text-xl font-bold text-gray-800 sm:text-2xl">
                        {{ topRatedPainting.title }}
                    </h2>

                    <p class="mt-1 text-gray-600">
                        {{ topRatedPainting.artist || "Anónimo" }}
                    </p>

                    <p class="mt-3 text-lg font-semibold text-amber-600">
                        ⭐ {{ topRatedPainting.ratings_avg }} / 5
                        <span class="text-sm font-normal text-gray-500">
                            ({{ topRatedPainting.ratings_count }} valoraciones)
                        </span>
                    </p>

                    <Link
                        :href="`/cuadros/${topRatedPainting.id}`"
                        class="mt-4 inline-block text-sm text-blue-600 hover:underline"
                    >
                        Ver la obra →
                    </Link>
                </div>
            </div>
        </section>

        <section v-if="latestPublications.length" class="mt-8 sm:mt-10">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 class="text-xl font-bold text-gray-800 sm:text-2xl">
                    Últimas publicaciones
                </h2>
                <Link
                    href="/publicaciones"
                    class="text-sm text-blue-600 hover:underline"
                >
                    Ver todas
                </Link>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="publication in latestPublications"
                    :key="publication.id"
                    :href="`/publicaciones/${publication.id}`"
                    class="overflow-hidden rounded-xl bg-white shadow-sm hover:shadow-md"
                >
                    <div
                        v-if="publication.image_url"
                        class="flex h-32 items-center justify-center bg-gray-100"
                    >
                        <img
                            :src="publication.image_url"
                            :alt="publication.title"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div class="p-5">
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
                    </div>
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
    topRatedPainting: {
        type: Object,
        default: null,
    },
});
</script>
