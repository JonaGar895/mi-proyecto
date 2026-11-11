<template>
    <Head :title="exhibition.title" />

    <Layout>
        <Link href="/exposiciones" class="text-sm text-blue-600 hover:underline">
            ← Volver a las exposiciones
        </Link>

        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="break-words text-2xl font-bold text-gray-800 sm:text-3xl">
                        {{ exhibition.title }}
                    </h1>

                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold text-white"
                        :class="stateClass(exhibition.state)"
                    >
                        {{ stateLabel(exhibition.state) }}
                    </span>
                </div>

                <p class="mt-2 text-sm text-gray-500">
                    <span v-if="exhibition.starts_at">Desde {{ formatDate(exhibition.starts_at) }}</span>
                    <span v-if="exhibition.ends_at"> · hasta {{ formatDate(exhibition.ends_at) }}</span>
                    <span v-if="!exhibition.starts_at && !exhibition.ends_at">
                        Fecha sin definir
                    </span>
                </p>

                <p v-if="exhibition.organizer" class="mt-1 text-sm text-gray-500">
                    Organizada por {{ exhibition.organizer.name }}
                </p>
            </div>

            <div v-if="canManage" class="flex flex-wrap gap-3">
                <Link
                    :href="`/exposiciones/${exhibition.id}/edit`"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700"
                >
                    Editar
                </Link>

                <button
                    type="button"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700"
                    @click="destroy"
                >
                    Eliminar
                </button>
            </div>
        </div>

        <p class="mt-4 whitespace-pre-line text-gray-700">
            {{ exhibition.description || "Sin descripción." }}
        </p>

        <section class="mt-8">
            <h2 class="text-xl font-bold text-gray-800 sm:text-2xl">
                Obras de la exposición ({{ exhibition.paintings_count }})
            </h2>

            <div
                v-if="paintings.data.length"
                class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <article
                    v-for="painting in paintings.data"
                    :key="painting.id"
                    class="overflow-hidden rounded-xl bg-white shadow-sm transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md"
                >
                    <Link :href="`/cuadros/${painting.id}`" class="block">
                        <div
                            class="flex aspect-[4/3] items-center justify-center bg-gray-100 text-6xl"
                        >
                            <img
                                v-if="painting.image_url"
                                :src="painting.image_url"
                                :alt="painting.title"
                                class="h-full w-full object-contain"
                            />
                            <span v-else>{{ painting.emoji }}</span>
                        </div>
                    </Link>

                    <div class="p-4">
                        <Link :href="`/cuadros/${painting.id}`" class="min-w-0">
                            <h3 class="break-words font-semibold text-gray-800 hover:underline">
                                {{ painting.title }}
                            </h3>
                        </Link>

                        <p class="mt-1 text-sm text-gray-600">{{ painting.artist || "Anónimo" }}</p>

                        <p class="mt-2 text-xs uppercase tracking-wide text-gray-500">
                            {{ painting.category || "Sin categoría" }}
                            <span v-if="painting.year"> · {{ painting.year }}</span>
                        </p>

                        <div
                            class="mt-3 flex flex-wrap items-center justify-between gap-2 text-sm text-gray-500"
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

                        <p
                            v-if="painting.status === 'sold'"
                            class="mt-2 text-xs font-semibold text-red-600"
                        >
                            Vendida
                        </p>
                    </div>
                </article>
            </div>

            <p v-else class="mt-6 rounded-xl bg-white p-6 text-gray-500 shadow-sm">
                Esta exposición todavía no tiene obras asignadas.
            </p>

            <Pagination :links="paintings.links" class="mt-8" />
        </section>
    </Layout>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import Pagination from "../../shared/Pagination.vue";
import HeroIcon from "../../shared/HeroIcon.vue";

const props = defineProps({
    exhibition: {
        type: Object,
        required: true,
    },
    paintings: {
        type: Object,
        required: true,
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

const destroy = () => {
    if (confirm(`¿Seguro que quieres eliminar la exposición "${props.exhibition.title}"?`)) {
        router.delete(`/exposiciones/${props.exhibition.id}`);
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

const formatPrice = (price) =>
    Number(price).toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
    });
</script>
