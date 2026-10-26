<template>
    <Head :title="user.name" />

    <Layout>
        <Link
            href="/users/list"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
            ← Volver a la lista
        </Link>

        <div class="mt-4 rounded-xl bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800">
                        {{ user.name }}
                    </h1>
                    <p class="mt-1 text-gray-600">{{ user.email }}</p>
                    <p class="mt-1 text-sm text-gray-400">ID {{ user.id }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        :href="`/users/edit/${user.id}`"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        Editar
                    </Link>

                    <button
                        v-if="user.id !== $page.props.auth.user.id"
                        type="button"
                        class="text-red-600 hover:underline"
                        @click="deleteUser"
                    >
                        Eliminar
                    </button>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Obras publicadas
                    </p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">
                        {{ stats.paintings }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Favoritos
                    </p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">
                        {{ stats.favorites }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Comentarios
                    </p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">
                        {{ stats.comments }}
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({ paintings: 0, favorites: 0, comments: 0 }),
    },
});

const deleteUser = () => {
    if (
        confirm(
            `¿Eliminar a "${props.user.name}"? También se borrarán sus cuadros, favoritos y comentarios.`,
        )
    ) {
        router.post(`/users/delete/${props.user.id}`);
    }
};
</script>
