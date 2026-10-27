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

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">
                    Publicaciones
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800">
                    {{ stats.publications }}
                </p>
            </div>
        </div>

        <section class="mt-10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-2xl font-bold text-gray-800">
                    Mis publicaciones
                </h2>

                <Link
                    href="/publicaciones/create"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700"
                >
                    + Nueva publicación
                </Link>
            </div>

            <div v-if="myPublications.length" class="mt-4 space-y-3">
                <article
                    v-for="publication in myPublications"
                    :key="publication.id"
                    class="flex flex-wrap items-start justify-between gap-4 rounded-xl bg-white p-4 shadow-sm"
                >
                    <div class="min-w-0">
                        <Link
                            :href="`/publicaciones/${publication.id}`"
                            class="block font-medium text-gray-800 hover:underline"
                        >
                            {{ publication.title }}
                        </Link>

                        <div
                            class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500"
                        >
                            <span
                                v-if="publication.is_published"
                                class="rounded-full bg-green-100 px-2 py-0.5 text-green-700"
                            >
                                Publicada
                            </span>
                            <span
                                v-else
                                class="rounded-full bg-amber-100 px-2 py-0.5 text-amber-700"
                            >
                                Borrador
                            </span>

                            <span v-if="publication.category">
                                {{ publication.category }}
                            </span>
                            <span>{{ publication.date }}</span>
                            <span>💬 {{ publication.comments_count }}</span>
                        </div>

                        <p
                            v-if="publication.summary"
                            class="mt-2 line-clamp-2 text-sm text-gray-600"
                        >
                            {{ publication.summary }}
                        </p>
                    </div>

                    <div class="flex gap-3 text-sm">
                        <Link
                            :href="`/publicaciones/${publication.id}`"
                            class="text-blue-600 hover:underline"
                        >
                            Ver
                        </Link>
                        <Link
                            :href="`/publicaciones/${publication.id}/edit`"
                            class="text-gray-600 hover:underline"
                        >
                            Editar
                        </Link>
                    </div>
                </article>
            </div>

            <p v-else class="mt-4 text-gray-500">
                Todavía no has escrito ninguna publicación.
                <Link
                    href="/publicaciones/create"
                    class="text-blue-600 hover:underline"
                >
                    Crea la primera
                </Link>.
            </p>
        </section>

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
        default: () => ({
            paintings: 0,
            favorites: 0,
            comments: 0,
            publications: 0,
        }),
    },
    favoritePaintings: {
        type: Array,
        default: () => [],
    },
    myPublications: {
        type: Array,
        default: () => [],
    },
});
</script>
