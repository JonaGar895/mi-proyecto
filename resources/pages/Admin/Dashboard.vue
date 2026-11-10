<template>
    <Head title="Panel de administración" />

    <Layout>
        <h1 class="text-3xl font-bold text-gray-800 sm:text-4xl">📊 Panel de administración</h1>
        <p class="mt-2 text-gray-600">
            Resumen de la actividad de la galería: obras, ventas, ingresos y solicitudes.
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="(stat, index) in statCards"
                :key="index"
                class="rounded-xl bg-white p-6 shadow-sm"
            >
                <p class="text-sm uppercase tracking-wide text-gray-500">
                    {{ stat.label }}
                </p>
                <p class="mt-2 text-3xl font-bold" :class="stat.color">
                    {{ stat.value }}
                </p>
            </div>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl bg-white p-4 shadow-sm sm:p-6">
                <h2 class="text-xl font-semibold text-gray-800">Ventas por mes</h2>

                <div class="mt-6 flex items-end justify-between gap-2">
                    <div
                        v-for="month in salesByMonth"
                        :key="month.month"
                        class="flex flex-1 flex-col items-center gap-2"
                    >
                        <span class="text-xs font-semibold text-gray-600">
                            {{ month.sales }}
                        </span>

                        <div
                            class="w-full rounded-t-lg bg-blue-500 transition-all duration-500"
                            :style="{ height: `${barHeight(month.sales)}px` }"
                            :title="`${month.sales} venta(s) · ${formatPrice(month.revenue)}`"
                        ></div>

                        <span class="text-center text-xs text-gray-500">
                            {{ month.month }}
                        </span>
                    </div>
                </div>

                <p class="mt-4 text-sm text-gray-500">
                    Altura proporcional al número de ventas aprobadas. Ingresa el cursor para ver los ingresos.
                </p>
            </section>

            <section class="rounded-xl bg-white p-4 shadow-sm sm:p-6">
                <h2 class="text-xl font-semibold text-gray-800">Obras por categoría</h2>

                <ul class="mt-6 space-y-3">
                    <li v-for="category in categories" :key="category.name">
                        <div class="flex items-center justify-between text-sm text-gray-600">
                            <span>{{ category.name }}</span>
                            <span class="font-semibold">{{ category.paintings }}</span>
                        </div>

                        <div class="mt-1 h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                            <div
                                class="h-full rounded-full bg-emerald-500"
                                :style="{ width: `${categoryWidth(category.paintings)}%` }"
                            ></div>
                        </div>
                    </li>

                    <li v-if="!categories.length" class="text-sm text-gray-500">
                        Todavía no hay categorías con obras.
                    </li>
                </ul>
            </section>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl bg-white p-4 shadow-sm sm:p-6">
                <h2 class="text-xl font-semibold text-gray-800">Artistas más activos</h2>

                <table class="mt-4 w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="pb-2">Artista</th>
                            <th class="pb-2 text-right">Obras</th>
                            <th class="pb-2 text-right">Vendidas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="artist in topArtists" :key="artist.id">
                            <td class="py-2 text-gray-700">
                                <Link :href="`/users/view/${artist.id}`" class="hover:underline">
                                    {{ artist.name }}
                                </Link>
                            </td>
                            <td class="py-2 text-right font-semibold text-gray-800">
                                {{ artist.paintings }}
                            </td>
                            <td class="py-2 text-right text-emerald-700">
                                {{ artist.sold }}
                            </td>
                        </tr>

                        <tr v-if="!topArtists.length">
                            <td colspan="3" class="py-3 text-gray-500">
                                Todavía no hay artistas con obras.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="rounded-xl bg-white p-4 shadow-sm sm:p-6">
                <h2 class="text-xl font-semibold text-gray-800">
                    Solicitudes pendientes
                    <span
                        v-if="stats.pending_inquiries"
                        class="ml-1 rounded-full bg-amber-500 px-2 py-0.5 align-middle text-xs font-semibold text-white"
                    >
                        {{ stats.pending_inquiries }}
                    </span>
                </h2>

                <ul class="mt-4 space-y-3">
                    <li
                        v-for="inquiry in pendingInquiries"
                        :key="inquiry.id"
                        class="flex items-center gap-3 rounded-lg border p-3 text-sm"
                    >
                        <span class="text-2xl leading-none">{{ inquiry.painting.emoji }}</span>

                        <div class="min-w-0 flex-1">
                            <Link
                                :href="`/cuadros/${inquiry.painting.id}`"
                                class="font-medium text-gray-800 hover:underline"
                            >
                                {{ inquiry.painting.title }}
                            </Link>
                            <p class="mt-0.5 text-xs text-gray-500">
                                {{ inquiry.buyer }} → {{ inquiry.seller }} ·
                                {{ inquiry.created_at }}
                            </p>
                        </div>

                        <span v-if="inquiry.price" class="font-semibold text-gray-700">
                            {{ formatPrice(inquiry.price) }}
                        </span>
                    </li>

                    <li v-if="!pendingInquiries.length" class="text-sm text-gray-500">
                        No hay solicitudes pendientes de revisión.
                    </li>
                </ul>

                <Link
                    href="/inquiries"
                    class="mt-4 inline-block text-sm text-blue-600 hover:underline"
                >
                    Gestionar solicitudes →
                </Link>
            </section>
        </div>

        <section class="mt-6 rounded-xl bg-white p-4 shadow-sm sm:p-6">
            <h2 class="text-xl font-semibold text-gray-800">Últimas ventas</h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="sale in recentSales"
                    :key="sale.id"
                    class="flex items-center gap-3 rounded-lg border p-3"
                >
                    <span class="text-2xl leading-none">{{ sale.painting.emoji }}</span>

                    <div class="min-w-0 flex-1">
                        <Link
                            :href="`/cuadros/${sale.painting.id}`"
                            class="block truncate text-sm font-medium text-gray-800 hover:underline"
                        >
                            {{ sale.painting.title }}
                        </Link>
                        <p class="mt-0.5 text-xs text-gray-500">
                            {{ sale.buyer }} compró a {{ sale.seller }} · {{ sale.approved_at }}
                        </p>
                    </div>

                    <span v-if="sale.price" class="text-sm font-semibold text-emerald-700">
                        {{ formatPrice(sale.price) }}
                    </span>
                </article>
            </div>

            <p v-if="!recentSales.length" class="mt-3 text-sm text-gray-500">
                Todavía no se ha aprobado ninguna venta.
            </p>
        </section>
    </Layout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    salesByMonth: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    topArtists: {
        type: Array,
        default: () => [],
    },
    pendingInquiries: {
        type: Array,
        default: () => [],
    },
    recentSales: {
        type: Array,
        default: () => [],
    },
});

const statCards = computed(() => [
    { label: "Usuarios", value: props.stats.users, color: "text-gray-800" },
    { label: "Obras", value: props.stats.paintings, color: "text-gray-800" },
    { label: "Obras vendidas", value: props.stats.sold_paintings, color: "text-red-600" },
    { label: "Ingresos totales", value: formatPrice(props.stats.revenue), color: "text-emerald-600" },
    { label: "Publicaciones", value: props.stats.publications, color: "text-gray-800" },
    { label: "Exposiciones", value: props.stats.exhibitions, color: "text-gray-800" },
    { label: "Comentarios", value: props.stats.comments, color: "text-gray-800" },
    { label: "Solicitudes pendientes", value: props.stats.pending_inquiries, color: "text-amber-600" },
]);

const maxSales = computed(() =>
    Math.max(1, ...props.salesByMonth.map((month) => month.sales)),
);

const maxCategoryPaintings = computed(() =>
    Math.max(1, ...props.categories.map((category) => category.paintings)),
);

const barHeight = (sales) => Math.max(4, Math.round((sales / maxSales.value) * 140));

const categoryWidth = (paintings) =>
    Math.max(2, Math.round((paintings / maxCategoryPaintings.value) * 100));

const formatPrice = (price) =>
    Number(price || 0).toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
    });
</script>
