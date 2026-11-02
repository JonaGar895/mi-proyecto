<template>
    <Head :title="user.name" />

    <Layout>
        <Link
            href="/users/list"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
            ← Volver a la lista
        </Link>

        <div class="mt-4 rounded-xl bg-white p-4 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <h1 class="break-words text-3xl font-bold text-gray-800 sm:text-4xl">
                        {{ user.name }}
                    </h1>
                    <p class="mt-1 break-words text-gray-600">{{ user.email }}</p>
                    <p class="mt-1 text-sm text-gray-400">ID {{ user.id }}</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Link
                        :href="`/users/edit/${user.id}?from=view`"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700 sm:w-auto"
                    >
                        Editar
                    </Link>

                    <button
                        v-if="user.id !== $page.props.auth.user.id"
                        type="button"
                        class="w-full text-center text-red-600 hover:underline sm:w-auto"
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
