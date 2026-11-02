<template>
    <Head title="Cuenta del super administrador" />

    <Layout>
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-gray-800">Cuenta del super administrador</h1>
            <p class="mt-2 text-gray-600">
                Aquí se concentra el dinero de las obras vendidas. Cada vez que se apruebe una venta, el importe se descuenta del saldo del comprador y se ingresa en esta cuenta separada.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">Saldo de esta cuenta</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700">{{ formatPrice(account.balance) }}</p>
                <p class="mt-1 text-xs text-gray-400" v-if="account.owner">
                    Vinculada a {{ account.owner.name }} ({{ account.owner.email }})
                </p>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">Ingresos totales</p>
                <p class="mt-2 text-3xl font-bold text-gray-800">{{ formatPrice(stats.total_income) }}</p>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">Ventas registradas</p>
                <p class="mt-2 text-3xl font-bold text-gray-800">{{ stats.sales_count }}</p>
            </div>
        </div>

        <section class="mt-8 rounded-xl bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Movimientos</h2>
                    <p class="mt-1 text-sm text-gray-500">Historial de ingresos por ventas aprobadas.</p>
                </div>
            </div>

            <div v-if="transactions.data.length" class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Obra</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Comprador</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Vendedor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Descripción</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Importe</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="tx in transactions.data" :key="tx.id">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ tx.created_at }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                <span class="mr-1">{{ tx.painting?.emoji || '🎨' }}</span>
                                <span>{{ tx.painting?.title || '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ tx.buyer || '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ tx.seller || '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ tx.description || '-' }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-700">+ {{ formatPrice(tx.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="mt-4 text-sm text-gray-500">Aún no hay ventas registradas en esta cuenta.</p>

            <Pagination :links="transactions.links" class="mt-6" />
        </section>
    </Layout>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "../../shared/Layout.vue";
import Pagination from "../../shared/Pagination.vue";

defineProps({
    account: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    transactions: {
        type: Object,
        required: true,
    },
});

const formatPrice = (price) => {
    if (price === null || price === undefined || price === "") {
        return "-";
    }

    const value = Number(price);

    if (Number.isNaN(value)) {
        return price;
    }

    return value.toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
    });
};
</script>
