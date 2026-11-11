<template>
    <Head title="Exposiciones" />

    <Layout>
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-3xl font-bold text-gray-800 sm:text-4xl">
                    <HeroIcon name="building-library" class="h-8 w-8 text-indigo-600" />
                    Exposiciones
                </h1>
                <p class="mt-2 text-gray-600">
                    Colecciones temáticas de obras organizadas por la galería.
                </p>
            </div>

            <Link
                v-if="canManage"
                href="/exposiciones/create"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700 sm:w-auto"
            >
                + Nueva exposición
            </Link>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <input
                v-model="search"
                type="text"
                placeholder="Buscar por título o descripción..."
                class="w-full rounded-lg border px-3 py-2 sm:max-w-md"
            />

            <select v-model="state" class="w-full rounded-lg border px-3 py-2 sm:w-auto">
                <option value="">Todos los estados</option>
                <option value="active">Activas</option>
                <option value="upcoming">Próximas</option>
                <option value="finished">Finalizadas</option>
            </select>
        </div>

        <div
            v-if="exhibitions.data.length"
            class="mt-8 grid grid-cols-1 gap-6 sm:mt-10 sm:grid-cols-2 lg:grid-cols-3"
        >
            <article
                v-for="exhibition in exhibitions.data"
                :key="exhibition.id"
                class="overflow-hidden rounded-xl bg-white shadow-sm transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md"
            >
                <Link :href="`/exposiciones/${exhibition.id}`" class="block">
                    <div
                        class="relative flex aspect-[4/3] items-center justify-center bg-gray-100"
                    >
                        <img
                            v-if="exhibition.cover"
                            :src="exhibition.cover"
                            :alt="exhibition.title"
                            class="h-full w-full object-scale-down"
                        />
                        <HeroIcon
                            v-else
                            name="building-library"
                            class="h-16 w-16 text-gray-400"
                        />

                        <span
                            class="absolute left-2 top-2 rounded-full px-2 py-1 text-xs font-semibold text-white"
                            :class="stateClass(exhibition.state)"
                        >
                            {{ stateLabel(exhibition.state) }}
                        </span>
                    </div>
                </Link>

                <div class="p-4 sm:p-6">
                    <Link :href="`/exposiciones/${exhibition.id}`" class="min-w-0">
                        <h2
                            class="break-words text-lg font-semibold text-gray-800 hover:underline"
                        >
                            {{ exhibition.title }}
                        </h2>
                    </Link>

                    <p class="mt-2 line-clamp-2 text-sm text-gray-600">
                        {{ exhibition.description || "Sin descripción." }}
                    </p>

                    <p class="mt-2 text-xs uppercase tracking-wide text-gray-500">
                        {{ exhibition.paintings_count }} obra(s)
                    </p>

                    <p v-if="exhibition.starts_at || exhibition.ends_at" class="mt-1 text-sm text-gray-500">
                        <span v-if="exhibition.starts_at">Desde {{ formatDate(exhibition.starts_at) }}</span>
                        <span v-if="exhibition.ends_at"> · hasta {{ formatDate(exhibition.ends_at) }}</span>
                    </p>

                    <div v-if="exhibition.can_manage" class="mt-4 flex flex-wrap gap-3 text-sm">
                        <Link
                            :href="`/exposiciones/${exhibition.id}/edit`"
                            class="text-blue-600 hover:underline"
                        >
                            Editar
                        </Link>

                        <button
                            type="button"
                            class="text-red-600 hover:underline"
                            @click="destroy(exhibition)"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <p v-else class="mt-8 rounded-xl bg-white p-6 text-gray-500 shadow-sm sm:mt-10 sm:p-8">
            No se encontraron exposiciones con esos filtros.
        </p>

        <Pagination :links="exhibitions.links" class="mt-8" />
    </Layout>
</template>

<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import debounce from "lodash/debounce";
import Pagination from "../../shared/Pagination.vue";
import HeroIcon from "../../shared/HeroIcon.vue";

const props = defineProps({
    exhibitions: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: "", state: "" }),
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

const search = ref(props.filters?.search ?? "");
const state = ref(props.filters?.state ?? "");

const applyFilters = debounce(() => {
    router.get(
        "/exposiciones",
        {
            search: search.value,
            state: state.value,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
}, 300);

watch([search, state], applyFilters);

const destroy = (exhibition) => {
    if (confirm(`¿Seguro que quieres eliminar la exposición "${exhibition.title}"?`)) {
        router.delete(`/exposiciones/${exhibition.id}`);
    }
};

const stateLabel = (state) =>
    ({ active: "Activa", upcoming: "Próxima", finished: "Finalizada" })[state] || "Activa";

const stateClass = (state) =>
    ({
        active: "bg-emerald-600",
        upcoming: "bg-blue-600",
        finished: "bg-gray-500",
    })[state] || "bg-emerald-600";

const formatDate = (date) =>
    new Date(date).toLocaleDateString("es-ES", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
</script>
