<template>
    <Head title="Mis obras" />

    <Layout>
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-3xl font-bold text-gray-800 sm:text-4xl">
                    <HeroIcon name="photo" class="h-8 w-8 text-indigo-600" />
                    Mis obras
                </h1>
                <p class="mt-2 text-gray-600">
                    Gestiona las obras que has publicado: edítalas, elimínalas o consulta su estado.
                </p>
            </div>

            <Link
                href="/cuadros/create"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700 sm:w-auto"
            >
                + Nueva obra
            </Link>
        </div>

        <div
            v-if="paintings.data.length"
            class="mt-8 grid grid-cols-1 gap-6 sm:mt-10 sm:grid-cols-2 lg:grid-cols-3"
        >
            <article
                v-for="painting in paintings.data"
                :key="painting.id"
                class="overflow-hidden rounded-xl bg-white shadow-sm transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md"
            >
                <Link :href="`/cuadros/${painting.id}`" class="block">
                    <div class="relative flex aspect-[4/3] items-center justify-center bg-gray-100 text-6xl">
                        <img
                            v-if="painting.image_url"
                            :src="painting.image_url"
                            :alt="painting.title"
                            class="h-full w-full object-scale-down"
                        />
                        <span v-else>{{ painting.emoji }}</span>

                        <span
                            v-if="!painting.is_public"
                            class="absolute left-2 top-2 rounded-full bg-gray-700 px-2 py-1 text-xs font-semibold text-white"
                        >
                            Privada
                        </span>

                        <span
                            class="absolute right-2 top-2 rounded-full px-2 py-1 text-xs font-semibold text-white"
                            :class="statusClass(painting.status)"
                        >
                            {{ statusLabel(painting.status) }}
                        </span>
                    </div>
                </Link>

                <div class="p-4">
                    <Link :href="`/cuadros/${painting.id}`" class="min-w-0">
                        <h2 class="break-words text-lg font-semibold text-gray-800 hover:underline">
                            {{ painting.title }}
                        </h2>
                    </Link>

                    <p class="mt-1 text-xs uppercase tracking-wide text-gray-500">
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
                        <span v-if="painting.price" class="font-medium text-gray-700">
                            {{ formatPrice(painting.price) }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3 text-sm">
                        <Link
                            :href="`/cuadros/${painting.id}/edit`"
                            class="text-blue-600 hover:underline"
                        >
                            Editar
                        </Link>

                        <button
                            type="button"
                            class="text-red-600 hover:underline"
                            @click="destroy(painting)"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <p v-else class="mt-8 rounded-xl bg-white p-6 text-gray-500 shadow-sm sm:mt-10 sm:p-8">
            Todavía no has publicado ninguna obra.
            <Link href="/cuadros/create" class="text-blue-600 hover:underline">
                Publica la primera
            </Link>
            .
        </p>

        <Pagination :links="paintings.links" class="mt-8" />
    </Layout>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import Pagination from "../../shared/Pagination.vue";
import HeroIcon from "../../shared/HeroIcon.vue";

defineProps({
    paintings: {
        type: Object,
        required: true,
    },
});

const destroy = (painting) => {
    if (confirm(`¿Seguro que quieres eliminar "${painting.title}"?`)) {
        router.delete(`/cuadros/${painting.id}`);
    }
};

const statusLabel = (status) =>
    ({ sold: "Vendida", reserved: "Reservada", available: "Disponible" })[status] || "Disponible";

const statusClass = (status) =>
    ({
        sold: "bg-red-600",
        reserved: "bg-amber-500",
        available: "bg-emerald-600",
    })[status] || "bg-emerald-600";

const formatPrice = (price) =>
    Number(price).toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
    });
</script>
