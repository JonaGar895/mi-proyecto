<template>
    <Head title="Publicaciones" />

    <Layout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-800">📰 Publicaciones</h1>
                <p class="mt-2 text-gray-600">
                    Noticias, artículos y reflexiones sobre las obras de la galería.
                </p>
            </div>

            <Link
                href="/publicaciones/create"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
            >
                + Nueva publicación
            </Link>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <input
                v-model="search"
                type="text"
                placeholder="Buscar por título, autor o contenido..."
                class="w-full rounded-lg border px-3 py-2 sm:max-w-md"
            />

            <select v-model="category" class="rounded-lg border px-3 py-2">
                <option value="">Todas las categorías</option>
                <option
                    v-for="item in categories"
                    :key="item.id"
                    :value="item.slug"
                >
                    {{ item.name }}
                </option>
            </select>
        </div>

        <div
            v-if="publications.data.length"
            class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
            <article
                v-for="publication in publications.data"
                :key="publication.id"
                class="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100"
            >
                <Link :href="`/publicaciones/${publication.id}`" class="block">
                    <div
                        class="flex h-40 items-center justify-center bg-gray-100 text-5xl"
                    >
                        <img
                            v-if="publication.painting?.image_url"
                            :src="publication.painting.image_url"
                            :alt="publication.painting.title"
                            class="h-full w-full object-cover"
                        />
                        <span v-else>📰</span>
                    </div>
                </Link>

                <div class="flex flex-1 flex-col p-6">
                    <div
                        class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-wide text-gray-500"
                    >
                        <span v-if="publication.category">
                            {{ publication.category.name }}
                        </span>

                        <span
                            v-if="!publication.is_published"
                            class="rounded-full bg-amber-100 px-2 py-0.5 text-amber-700"
                        >
                            Borrador
                        </span>
                    </div>

                    <Link :href="`/publicaciones/${publication.id}`">
                        <h2
                            class="mt-2 text-lg font-semibold text-gray-800 hover:underline"
                        >
                            {{ publication.title }}
                        </h2>
                    </Link>

                    <p class="mt-2 line-clamp-3 text-sm text-gray-600">
                        {{ publication.summary || "Sin resumen." }}
                    </p>

                    <div
                        class="mt-4 flex items-center justify-between text-xs text-gray-500"
                    >
                        <span>{{ publication.author?.name ?? "Anónimo" }}</span>
                        <span>{{ publication.date }}</span>
                    </div>

                    <div class="mt-2 flex items-center gap-3 text-sm text-gray-500">
                        <span>💬 {{ publication.comments_count }}</span>
                        <span
                            v-if="publication.painting"
                            class="truncate"
                            :title="publication.painting.title"
                        >
                            🖼️ {{ publication.painting.title }}
                        </span>
                    </div>
                </div>
            </article>
        </div>

        <p
            v-else
            class="mt-10 rounded-xl bg-white p-8 text-gray-500 shadow-sm"
        >
            Todavía no hay publicaciones. ¡Anímate a escribir la primera!
        </p>

        <Pagination :links="publications.links" class="mt-8" />
    </Layout>
</template>

<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import debounce from "lodash/debounce";
import Pagination from "../../shared/Pagination.vue";

const props = defineProps({
    publications: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ search: "", category: "" }),
    },
});

const search = ref(props.filters?.search ?? "");
const category = ref(props.filters?.category ?? "");

const applyFilters = debounce(() => {
    router.get(
        "/publicaciones",
        {
            search: search.value,
            category: category.value,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
}, 300);

watch([search, category], applyFilters);
</script>
