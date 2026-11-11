<template>
    <Head title="Publicaciones" />

    <Layout>
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-3xl font-bold text-gray-800 sm:text-4xl">
                    <HeroIcon name="newspaper" class="h-8 w-8 text-indigo-600" />
                    Publicaciones
                </h1>
                <p class="mt-2 text-gray-600">
                    Noticias, artículos y reflexiones sobre las obras de la galería.
                </p>
            </div>

            <Link
                href="/publicaciones/create"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700 sm:w-auto"
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

            <select v-model="category" class="w-full rounded-lg border px-3 py-2 sm:w-auto">
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
            class="mt-8 grid grid-cols-1 gap-6 sm:mt-10 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4"
        >
            <article
                v-for="publication in publications.data"
                :key="publication.id"
                class="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md"
            >
                <Link :href="`/publicaciones/${publication.id}`" class="block">
                    <div class="bg-gray-100">
                        <img
                            v-if="imageFor(publication)"
                            :src="imageFor(publication)"
                            :alt="publication.title"
                            class="block h-auto w-full"
                        />
                        <span
                            v-else
                            class="flex h-48 w-full items-center justify-center"
                        >
                            <HeroIcon
                                name="newspaper"
                                class="h-14 w-14 text-gray-400"
                            />
                        </span>
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
                        <span class="inline-flex items-center gap-1">
                            <HeroIcon name="chat-bubble-left" class="h-4 w-4 text-gray-400" />
                            {{ publication.comments_count }}
                        </span>
                        <span
                            v-if="publication.painting"
                            class="inline-flex items-center gap-1 truncate"
                            :title="publication.painting.title"
                        >
                            <HeroIcon name="photo" class="h-4 w-4 shrink-0 text-gray-400" />
                            {{ publication.painting.title }}
                        </span>
                    </div>
                </div>
            </article>
        </div>

        <p
            v-else
            class="mt-8 rounded-xl bg-white p-6 text-gray-500 shadow-sm sm:mt-10 sm:p-8"
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
import HeroIcon from "../../shared/HeroIcon.vue";

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

const imageFor = (publication) =>
    publication.image_url || publication.painting?.image_url || null;
</script>
