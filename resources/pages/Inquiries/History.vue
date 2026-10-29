<template>
    <Head title="Histórico de compras y ventas" />

    <Layout>
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-gray-800">Histórico de compras y ventas</h1>
            <p class="mt-2 text-gray-600">
                Solo visible para administradores y super administrador. Muestra fecha/hora de solicitud, aceptación o rechazo.
            </p>
            <Link href="/inquiries" class="mt-2 inline-block text-sm text-blue-600 hover:underline">
                ← Volver a Solicitudes
            </Link>
        </div>

        <div class="rounded-xl bg-white shadow-sm overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Obra</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Comprador</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Vendedor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Precio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fecha/Hora Solicitud</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fecha/Hora Aceptado/Rechazado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Gestionado por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="item in history" :key="item.id">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">#{{ item.id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <Link :href="`/cuadros/${item.painting?.id}`" class="hover:underline">
                                {{ item.painting?.title }}
                            </Link>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>{{ item.buyer?.name }}</div>
                            <div class="text-xs text-gray-500">{{ item.buyer?.email }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>{{ item.seller?.name }}</div>
                            <div class="text-xs text-gray-500">{{ item.seller?.email }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ formatPrice(item.painting?.price) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ item.created_at_formatted }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <span class="rounded-full px-2 py-1 text-xs" :class="statusClass(item.status)">
                                {{ statusLabel(item.status) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            <span v-if="item.status === 'approved' || item.status === 'closed' && item.approved_at_formatted">
                                {{ item.approved_at_formatted }}
                            </span>
                            <span v-else-if="item.status === 'rejected' || item.status === 'declined'">
                                {{ item.rejected_at_formatted }}
                            </span>
                            <span v-else>-</span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                            <span v-if="item.approved_by">{{ item.approved_by.name }}</span>
                            <span v-else-if="item.rejected_by">{{ item.rejected_by.name }}</span>
                            <span v-else>-</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!history.length" class="p-6 text-center text-sm text-gray-500">
                No hay registros en el histórico.
            </p>
        </div>
    </Layout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    history: { type: Array, default: () => [] },
});

const formatPrice = (price) => {
    if (price === null || price === undefined) return "-";
    const value = Number(price);
    if (Number.isNaN(value)) return price;
    return new Intl.NumberFormat("es-ES", {
        style: "currency",
        currency: "EUR",
    }).format(value);
};

const statusLabel = (status) => {
    const map = {
        pending: "Pendiente",
        contacted: "Contactado",
        declined: "Rechazado",
        rejected: "Rechazado",
        closed: "Cerrado",
        approved: "Aprobado/Vendido",
    };
    return map[status] || status;
};

const statusClass = (status) => {
    const map = {
        approved: "bg-emerald-100 text-emerald-700",
        closed: "bg-gray-100 text-gray-600",
        rejected: "bg-red-100 text-red-700",
        declined: "bg-red-100 text-red-700",
        pending: "bg-amber-100 text-amber-700",
    };
    return map[status] || "bg-gray-100 text-gray-600";
};
</script>
