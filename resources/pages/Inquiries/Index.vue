<template>
    <Head title="Solicitudes" />

    <Layout>
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-gray-800">Solicitudes</h1>
            <p class="mt-2 text-gray-600">Gestiona las solicitudes "Me interesa" recibidas y enviadas.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-800">Recibidas</h2>
                <p class="mt-1 text-sm text-gray-500">Personas interesadas en tus obras.</p>

                <div v-if="received.length" class="mt-4 space-y-4">
                    <article v-for="inq in received" :key="inq.id" class="rounded-lg border p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <Link :href="`/cuadros/${inq.painting.id}`" class="font-medium text-gray-800 hover:underline">
                                    {{ inq.painting.title }}
                                </Link>
                                <p class="text-sm text-gray-500">{{ inq.buyer.name }} · {{ inq.buyer.email }}</p>
                                <p v-if="inq.painting.price" class="text-sm text-gray-600">{{ formatPrice(inq.painting.price) }}</p>
                            </div>
                            <span
                                class="rounded-full px-2 py-1 text-xs"
                                :class="statusClass(inq.status)"
                            >
                                {{ statusLabel(inq.status) }}
                            </span>
                        </div>

                        <p v-if="inq.message" class="mt-2 text-sm text-gray-700">{{ inq.message }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ inq.created_at }}</p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" class="rounded-md border px-3 py-1 text-sm" @click="updateStatus(inq, 'contacted')">Marcar contactado</button>
                            <button type="button" class="rounded-md border px-3 py-1 text-sm" @click="updateStatus(inq, 'declined')">Rechazar</button>
                            <button type="button" class="rounded-md border px-3 py-1 text-sm" @click="updateStatus(inq, 'closed')">Cerrar</button>
                            <button
                                v-if="inq.painting.status !== 'sold'"
                                type="button"
                                class="rounded-md bg-emerald-600 px-3 py-1 text-sm text-white hover:bg-emerald-700"
                                @click="markSold(inq)"
                            >
                                Marcar como vendida
                            </button>
                        </div>
                    </article>
                </div>
                <p v-else class="mt-4 text-sm text-gray-500">No tienes solicitudes recibidas.</p>
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-800">Enviadas</h2>
                <p class="mt-1 text-sm text-gray-500">Obras en las que has mostrado interés.</p>

                <div v-if="sent.length" class="mt-4 space-y-4">
                    <article v-for="inq in sent" :key="inq.id" class="rounded-lg border p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <Link :href="`/cuadros/${inq.painting.id}`" class="font-medium text-gray-800 hover:underline">
                                    {{ inq.painting.title }}
                                </Link>
                                <p class="text-sm text-gray-500">Vendedor: {{ inq.seller.name }} · {{ inq.seller.email }}</p>
                                <p v-if="inq.painting.price" class="text-sm text-gray-600">{{ formatPrice(inq.painting.price) }}</p>
                            </div>
                            <span
                                class="rounded-full px-2 py-1 text-xs"
                                :class="statusClass(inq.status)"
                            >
                                {{ statusLabel(inq.status) }}
                            </span>
                        </div>

                        <p v-if="inq.message" class="mt-2 text-sm text-gray-700">{{ inq.message }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ inq.created_at }}</p>

                        <p v-if="inq.painting.status === 'sold'" class="mt-2 text-sm text-emerald-700">Esta obra ya está vendida.</p>
                    </article>
                </div>
                <p v-else class="mt-4 text-sm text-gray-500">No has enviado solicitudes.</p>
            </section>
        </div>
    </Layout>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
    received: { type: Array, default: () => [] },
    sent: { type: Array, default: () => [] },
});

const formatPrice = (price) => {
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
        closed: "Cerrado",
    };
    return map[status] || status;
};

const statusClass = (status) => {
    const map = {
        pending: "bg-amber-100 text-amber-700",
        contacted: "bg-blue-100 text-blue-700",
        declined: "bg-red-100 text-red-700",
        closed: "bg-gray-100 text-gray-600",
    };
    return map[status] || "bg-gray-100 text-gray-600";
};

const updateStatus = (inq, status) => {
    router.post(`/inquiries/${inq.id}/status`, { status }, { preserveScroll: true });
};

const markSold = (inq) => {
    if (confirm("¿Marcar esta obra como vendida y cerrar esta solicitud?")) {
        router.post(`/inquiries/${inq.id}/mark-sold`, {}, { preserveScroll: true });
    }
};
</script>
