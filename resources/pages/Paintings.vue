<template>
    <Head title="Cuadros" />

    <Layout>
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 sm:text-4xl">🖼️ Cuadros</h1>
                <p class="mt-2 text-gray-600">
                    Explora la galería, guarda tus favoritas y comenta las obras.
                </p>
            </div>

            <Link
                v-if="$page.props.auth?.user?.is_vendor || $page.props.auth?.user?.is_admin"
                href="/cuadros/create"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700 sm:w-auto"
            >
                + Nueva obra
            </Link>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <input
                v-model="search"
                type="text"
                placeholder="Buscar por título, artista o descripción..."
                class="w-full rounded-lg border px-3 py-2 sm:max-w-md"
            />

            <select
                v-model="category"
                class="w-full rounded-lg border px-3 py-2 sm:w-auto"
            >
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
            v-if="paintings.data.length"
            class="mt-8 grid grid-cols-1 gap-6 sm:mt-10 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4"
        >
            <article
                v-for="painting in paintings.data"
                :key="painting.id"
                class="overflow-hidden rounded-xl bg-white shadow-sm transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md"
            >
                <Link :href="`/cuadros/${painting.id}`" class="block">
                    <div
                        class="flex h-44 items-center justify-center bg-gray-100 text-6xl sm:h-48"
                    >
                        <img
                            v-if="painting.image_url"
                            :src="painting.image_url"
                            :alt="painting.title"
                            class="h-full w-full object-cover"
                        />
                        <span v-else>{{ painting.emoji }}</span>
                    </div>
                </Link>

                <div class="p-4 sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <Link :href="`/cuadros/${painting.id}`" class="min-w-0">
                            <h2
                                class="break-words text-lg font-semibold text-gray-800 hover:underline"
                            >
                                {{ painting.title }}
                            </h2>
                        </Link>

                        <button
                            type="button"
                            class="shrink-0 rounded-full px-2 py-1 text-xl leading-none"
                            :class="
                                painting.is_favorited
                                    ? 'text-red-500'
                                    : 'text-gray-400 hover:text-red-500'
                            "
                            :title="
                                painting.is_favorited
                                    ? 'Quitar de favoritos'
                                    : 'Añadir a favoritos'
                            "
                            @click="toggleFavorite(painting)"
                        >
                            {{ painting.is_favorited ? "♥" : "♡" }}
                        </button>
                    </div>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ painting.artist ? painting.artist.name : "Anónimo" }}
                    </p>

                    <p class="mt-2 text-xs uppercase tracking-wide text-gray-500">
                        {{ painting.category ? painting.category.name : "Sin categoría" }}
                        <span v-if="painting.year"> · {{ painting.year }}</span>
                    </p>

                    <p v-if="painting.technique" class="mt-1 text-sm text-gray-600">
                        {{ painting.technique }}
                    </p>

                    <div
                        class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm text-gray-500"
                    >
                        <span>♥ {{ painting.favorites_count }}</span>
                        <span>💬 {{ painting.comments_count }}</span>
                        <span v-if="painting.price">{{ formatPrice(painting.price) }}</span>
                    </div>
                </div>
            </article>
        </div>

        <p v-else class="mt-8 rounded-xl bg-white p-6 text-gray-500 shadow-sm sm:mt-10 sm:p-8">
            No se encontraron obras con esos filtros.
        </p>

        <Pagination :links="paintings.links" class="mt-8" />
    </Layout>
</template>

<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import debounce from "lodash/debounce";
import Pagination from "../shared/Pagination.vue";

const props = defineProps({
    paintings: {
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
        "/cuadros",
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

const toggleFavorite = (painting) => {
    router.post(
        `/cuadros/${painting.id}/favorite`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const formatPrice = (price) =>
    Number(price).toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
    });
</script>
