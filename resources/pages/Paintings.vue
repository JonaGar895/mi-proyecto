<template>
    <Head title="Cuadros" />

    <Layout>
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-3xl font-bold text-gray-800 sm:text-4xl">
                    <HeroIcon name="photo" class="h-8 w-8 text-indigo-600" />
                    Cuadros
                </h1>
                <p class="mt-2 text-gray-600">
                    Explora la galería, valora las obras, guarda tus favoritas y comenta.
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

            <select v-model="status" class="w-full rounded-lg border px-3 py-2 sm:w-auto">
                <option value="">Todos los estados</option>
                <option value="available">Disponibles</option>
                <option value="sold">Vendidas</option>
                <option value="reserved">Reservadas</option>
            </select>

            <select v-model="technique" class="w-full rounded-lg border px-3 py-2 sm:w-auto">
                <option value="">Todas las técnicas</option>
                <option v-for="item in techniques" :key="item" :value="item">
                    {{ item }}
                </option>
            </select>

            <select v-model="sort" class="w-full rounded-lg border px-3 py-2 sm:w-auto">
                <option value="recent">Más recientes</option>
                <option value="oldest">Más antiguas</option>
                <option value="favorites">Más favoritas</option>
                <option value="comments">Más comentadas</option>
                <option value="rating">Mejor valoradas</option>
                <option value="price_asc">Precio: menor a mayor</option>
                <option value="price_desc">Precio: mayor a menor</option>
            </select>
        </div>

        <div class="mt-3 flex flex-col gap-3 text-sm sm:flex-row sm:flex-wrap sm:items-center">
            <div class="flex items-center gap-2">
                <label for="price_min" class="whitespace-nowrap text-gray-600">
                    Precio mín.
                </label>
                <input
                    id="price_min"
                    v-model="priceMin"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="0"
                    class="w-24 rounded-lg border px-2 py-1.5"
                />
            </div>

            <div class="flex items-center gap-2">
                <label for="price_max" class="whitespace-nowrap text-gray-600">
                    Precio máx.
                </label>
                <input
                    id="price_max"
                    v-model="priceMax"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="∞"
                    class="w-24 rounded-lg border px-2 py-1.5"
                />
            </div>

            <div class="flex items-center gap-2">
                <label for="year" class="whitespace-nowrap text-gray-600">
                    Año
                </label>
                <input
                    id="year"
                    v-model="year"
                    type="number"
                    min="1000"
                    :max="new Date().getFullYear()"
                    placeholder="Cualquiera"
                    class="w-28 rounded-lg border px-2 py-1.5"
                />
            </div>

            <button
                v-if="hasActiveFilters"
                type="button"
                class="text-blue-600 hover:underline"
                @click="resetFilters"
            >
                Limpiar filtros
            </button>
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
                        class="relative flex aspect-[4/3] items-center justify-center bg-gray-100 text-6xl"
                    >
                        <img
                            v-if="painting.image_url"
                            :src="painting.image_url"
                            :alt="painting.title"
                            class="h-full w-full object-scale-down"
                        />
                        <span v-else>{{ painting.emoji }}</span>

                        <span
                            v-if="painting.status === 'sold'"
                            class="absolute right-2 top-2 rounded-full bg-red-600 px-2 py-1 text-xs font-semibold text-white"
                        >
                            Vendida
                        </span>

                        <span
                            v-else-if="painting.status === 'reserved'"
                            class="absolute right-2 top-2 rounded-full bg-amber-500 px-2 py-1 text-xs font-semibold text-white"
                        >
                            Reservada
                        </span>
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
                            class="shrink-0 rounded-full p-1.5"
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
                            <HeroIcon
                                name="heart"
                                :solid="painting.is_favorited"
                                class="h-6 w-6"
                            />
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
                        <span class="inline-flex items-center gap-1">
                            <HeroIcon name="heart" class="h-4 w-4 text-red-500" />
                            {{ painting.favorites_count }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <HeroIcon name="chat-bubble-left" class="h-4 w-4 text-gray-400" />
                            {{ painting.comments_count }}
                        </span>
                        <span
                            v-if="painting.ratings_avg"
                            class="inline-flex items-center gap-1 text-amber-600"
                        >
                            <HeroIcon name="star" class="h-4 w-4" />
                            {{ painting.ratings_avg }}
                        </span>
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
import { computed, ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import debounce from "lodash/debounce";
import Pagination from "../shared/Pagination.vue";
import HeroIcon from "../shared/HeroIcon.vue";

const props = defineProps({
    paintings: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    techniques: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters?.search ?? "");
const category = ref(props.filters?.category ?? "");
const technique = ref(props.filters?.technique ?? "");
const status = ref(props.filters?.status ?? "");
const sort = ref(props.filters?.sort ?? "recent");
const priceMin = ref(props.filters?.price_min ?? "");
const priceMax = ref(props.filters?.price_max ?? "");
const year = ref(props.filters?.year ?? "");

const hasActiveFilters = computed(
    () =>
        search.value !== "" ||
        category.value !== "" ||
        technique.value !== "" ||
        status.value !== "" ||
        sort.value !== "recent" ||
        priceMin.value !== "" ||
        priceMax.value !== "" ||
        year.value !== "",
);

const applyFilters = debounce(() => {
    router.get(
        "/cuadros",
        {
            search: search.value,
            category: category.value,
            technique: technique.value,
            status: status.value,
            sort: sort.value,
            price_min: priceMin.value,
            price_max: priceMax.value,
            year: year.value,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
}, 300);

watch([search, category, technique, status, sort, priceMin, priceMax, year], applyFilters);

const resetFilters = () => {
    search.value = "";
    category.value = "";
    technique.value = "";
    status.value = "";
    sort.value = "recent";
    priceMin.value = "";
    priceMax.value = "";
    year.value = "";
};

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
