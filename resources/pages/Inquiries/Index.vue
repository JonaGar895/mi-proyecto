
<template>
    <Head title="Solicitudes" />

    <Layout>
        <div class="mb-6 flex min-w-0 flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                <h1 class="break-words text-3xl font-bold text-gray-800 sm:text-4xl">
                    Solicitudes
                </h1>

                <p class="mt-2 break-words text-gray-600">
                    <template v-if="isAdminView">
                        Panel de administración. Solo los administradores o el super administrador (ID 1) pueden aprobar o rechazar las solicitudes de compra.
                    </template>
                    <template v-else>
                        Gestiona las solicitudes de compra recibidas y enviadas.
                    </template>
                </p>
            </div>

            <Link
                v-if="isAdminView"
                href="/inquiries/history"
                class="w-full rounded-lg bg-gray-800 px-4 py-2 text-center text-white hover:bg-gray-900 sm:w-auto"
            >
                Ver histórico de compras/ventas
            </Link>
        </div>

        <div v-if="isAdminView" class="min-w-0 w-full">
            <section class="w-full min-w-0 overflow-hidden rounded-xl bg-white p-3 shadow-sm sm:p-6">

                <h2 class="break-words text-xl font-semibold text-gray-800">
                    Todas las solicitudes de compra
                </h2>

                <p class="mt-1 break-words text-sm text-gray-500">
                    Requieren aprobación de un administrador para concretar la venta.
                </p>

                <div v-if="adminAll.length" class="mt-4 space-y-4">

                    <article
                        v-for="inq in adminAll"
                        :key="inq.id"
                        class="w-full min-w-0 overflow-hidden rounded-lg border p-3 sm:p-4"
                    >
                        <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="`/cuadros/${inq.painting.id}`"
                                    class="block max-w-full break-words font-medium text-gray-800 hover:underline"
                                >
                                    {{ inq.painting.title }}
                                </Link>

                                <p class="mt-1 min-w-0 break-words text-sm text-gray-500">
                                    Comprador: {{ inq.buyer?.name }} · {{ inq.buyer?.email }}
                                </p>

                                <p class="min-w-0 break-words text-sm text-gray-500">
                                    Vendedor: {{ inq.seller?.name }} · {{ inq.seller?.email }}
                                </p>

                                <p
                                    v-if="inq.painting.price"
                                    class="mt-1 break-words text-sm text-gray-600"
                                >
                                    {{ formatPrice(inq.painting.price) }}
                                </p>

                                <p class="mt-1 break-words text-xs text-gray-400">
                                    Creada: {{ inq.created_at }}
                                </p>
                            </div>

                            <div class="w-full min-w-0 sm:w-auto sm:shrink-0">
                                <span
                                    class="inline-flex max-w-full whitespace-normal break-words rounded-full px-3 py-1 text-xs"
                                    :class="statusClass(inq.status)"
                                >
                                    {{ statusLabel(inq.status) }}
                                </span>
                            </div>
                        </div>

                        <p
                            v-if="inq.message"
                            class="mt-3 min-w-0 break-words rounded-lg bg-gray-50 p-3 text-sm text-gray-700"
                        >
                            {{ inq.message }}
                        </p>

                        <div
                            v-if="inq.approved_by"
                            class="mt-2 break-words text-xs text-emerald-700"
                        >
                            Aprobada por: {{ inq.approved_by.name }}
                            <span v-if="inq.approved_at">
                                · {{ inq.approved_at }}
                            </span>
                        </div>

                        <div
                            v-if="inq.rejected_by"
                            class="mt-2 break-words text-xs text-red-700"
                        >
                            Rechazada por: {{ inq.rejected_by.name }}
                            <span v-if="inq.rejected_at">
                                · {{ inq.rejected_at }}
                            </span>
                        </div>

                        <div class="mt-3 flex min-w-0 flex-col gap-2 sm:flex-row sm:flex-wrap">

                            <button
                                v-if="inq.status === 'pending' && inq.painting.status !== 'sold'"
                                type="button"
                                class="w-full rounded-md bg-emerald-600 px-3 py-2 text-sm text-white transition hover:bg-emerald-700 sm:w-auto"
                                @click="approveInquiry(inq)"
                            >
                                Aprobar venta
                            </button>

                            <button
                                v-if="inq.status === 'pending' && inq.painting.status !== 'sold'"
                                type="button"
                                class="w-full rounded-md bg-red-600 px-3 py-2 text-sm text-white transition hover:bg-red-700 sm:w-auto"
                                @click="rejectInquiry(inq)"
                            >
                                Rechazar solicitud
                            </button>

                            <button
                                v-if="inq.status !== 'pending' && inq.painting.status === 'sold' && inq.status === 'approved'"
                                type="button"
                                class="w-full rounded-md border px-3 py-2 text-sm sm:w-auto"
                                disabled
                            >
                                Venta concretada
                            </button>

                        </div>
                    </article>

                </div>

                <p v-else class="mt-4 text-sm text-gray-500">
                    No hay solicitudes de compra.
                </p>
            </section>
        </div>


        <div
            v-else
            class="grid min-w-0 w-full grid-cols-1 gap-4 lg:grid-cols-2 xl:gap-6"
        >

            <section class="w-full min-w-0 overflow-hidden rounded-xl bg-white p-3 shadow-sm sm:p-6">

                <h2 class="break-words text-xl font-semibold text-gray-800">
                    Recibidas
                </h2>

                <p class="mt-1 break-words text-sm text-gray-500">
                    Solicitudes recibidas para tus obras (informativo).
                </p>

                <div v-if="received.length" class="mt-4 space-y-4">

                    <article
                        v-for="inq in received"
                        :key="inq.id"
                        class="w-full min-w-0 overflow-hidden rounded-lg border p-3 sm:p-4"
                    >
                        <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                            <div class="min-w-0 flex-1">

                                <Link
                                    :href="`/cuadros/${inq.painting.id}`"
                                    class="block max-w-full break-words font-medium text-gray-800 hover:underline"
                                >
                                    {{ inq.painting.title }}
                                </Link>

                                <p class="mt-1 min-w-0 break-words text-sm text-gray-500">
                                    {{ inq.buyer.name }} · {{ inq.buyer.email }}
                                </p>

                                <p
                                    v-if="inq.painting.price"
                                    class="mt-1 break-words text-sm text-gray-600"
                                >
                                    {{ formatPrice(inq.painting.price) }}
                                </p>
                            </div>

                            <div class="w-full min-w-0 sm:w-auto sm:shrink-0">
                                <span
                                    class="inline-flex max-w-full whitespace-normal break-words rounded-full px-3 py-1 text-xs"
                                    :class="statusClass(inq.status)"
                                >
                                    {{ statusLabel(inq.status) }}
                                </span>
                            </div>
                        </div>

                        <p
                            v-if="inq.message"
                            class="mt-2 min-w-0 break-words text-sm text-gray-700"
                        >
                            {{ inq.message }}
                        </p>

                        <p class="mt-1 break-words text-xs text-gray-400">
                            {{ inq.created_at }}
                        </p>
                    </article>

                </div>

                <p v-else class="mt-4 text-sm text-gray-500">
                    No tienes solicitudes recibidas.
                </p>
            </section>

            <section class="w-full min-w-0 overflow-hidden rounded-xl bg-white p-3 shadow-sm sm:p-6">

                <h2 class="break-words text-xl font-semibold text-gray-800">
                    Enviadas
                </h2>

                <p class="mt-1 break-words text-sm text-gray-500">
                    Solicitudes que has enviado para comprar obras.
                </p>

                <div v-if="sent.length" class="mt-4 space-y-4">

                    <article
                        v-for="inq in sent"
                        :key="inq.id"
                        class="w-full min-w-0 overflow-hidden rounded-lg border p-3 sm:p-4"
                    >
                        <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                            <div class="min-w-0 flex-1">

                                <Link
                                    :href="`/cuadros/${inq.painting.id}`"
                                    class="block max-w-full break-words font-medium text-gray-800 hover:underline"
                                >
                                    {{ inq.painting.title }}
                                </Link>

                                <p class="mt-1 min-w-0 break-words text-sm text-gray-500">
                                    Vendedor: {{ inq.seller.name }} · {{ inq.seller.email }}
                                </p>

                                <p
                                    v-if="inq.painting.price"
                                    class="mt-1 break-words text-sm text-gray-600"
                                >
                                    {{ formatPrice(inq.painting.price) }}
                                </p>
                            </div>

                            <div class="w-full min-w-0 sm:w-auto sm:shrink-0">
                                <span
                                    class="inline-flex max-w-full whitespace-normal break-words rounded-full px-3 py-1 text-xs"
                                    :class="statusClass(inq.status)"
                                >
                                    {{ statusLabel(inq.status) }}
                                </span>
                            </div>
                        </div>

                        <p
                            v-if="inq.message"
                            class="mt-2 min-w-0 break-words text-sm text-gray-700"
                        >
                            {{ inq.message }}
                        </p>

                        <p class="mt-1 break-words text-xs text-gray-400">
                            {{ inq.created_at }}
                        </p>

                        <p
                            v-if="inq.status === 'approved'"
                            class="mt-2 break-words text-sm text-emerald-700"
                        >
                            ¡Solicitud aprobada! La venta ha sido autorizada por un administrador.
                        </p>

                        <p
                            v-if="inq.status === 'rejected' || inq.status === 'declined'"
                            class="mt-2 break-words text-sm text-red-700"
                        >
                            Tu solicitud ha sido rechazada por un administrador.
                        </p>

                        <p
                            v-if="inq.status === 'pending'"
                            class="mt-2 break-words text-sm text-amber-700"
                        >
                            Pendiente de aprobación por un administrador.
                        </p>

                        <p
                            v-if="inq.painting.status === 'sold' && inq.status !== 'approved'"
                            class="mt-2 break-words text-sm text-gray-600"
                        >
                            Esta obra ya se encuentra vendida.
                        </p>
                    </article>

                </div>

                <p v-else class="mt-4 text-sm text-gray-500">
                    No has enviado solicitudes.
                </p>
            </section>
        </div>
    </Layout>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
    received: { type: Array, default: () => [] },
    sent: { type: Array, default: () => [] },
    adminAll: { type: Array, default: () => [] },
    isAdminView: { type: Boolean, default: false },
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
        pending: "Pendiente de aprobación",
        contacted: "Contactado",
        declined: "Rechazado",
        rejected: "Rechazado",
        closed: "Cerrado",
        approved: "Aprobado",
    };

    return map[status] || status;
};

const statusClass = (status) => {
    const map = {
        pending: "bg-amber-100 text-amber-700",
        contacted: "bg-blue-100 text-blue-700",
        declined: "bg-red-100 text-red-700",
        rejected: "bg-red-100 text-red-700",
        closed: "bg-gray-100 text-gray-600",
        approved: "bg-emerald-100 text-emerald-700",
    };

    return map[status] || "bg-gray-100 text-gray-600";
};


const updateStatus = (inq, status) => {
    router.post(
        `/inquiries/${inq.id}/status`,
        { status },
        { preserveScroll: true }
    );
};

const approveInquiry = (inq) => {
    if (
        confirm(
            "¿Aprobar esta solicitud de compra? Se marcará la obra como vendida, se descontará el importe al comprador y se ingresará en la cuenta del super administrador."
        )
    ) {
        router.post(
            `/inquiries/${inq.id}/approve`,
            {},
            { preserveScroll: true }
        );
    }
};

const rejectInquiry = (inq) => {
    if (confirm("¿Rechazar esta solicitud de compra?")) {
        router.post(
            `/inquiries/${inq.id}/reject`,
            {},
            { preserveScroll: true }
        );
    }
};

const markSold = (inq) => {
    if (
        confirm(
            "¿Marcar esta obra como vendida y cerrar esta solicitud? Se descontará el importe al comprador y se ingresará en la cuenta del super administrador."
        )
    ) {
        router.post(
            `/inquiries/${inq.id}/mark-sold`,
            {},
            { preserveScroll: true }
        );
    }
};
</script>
