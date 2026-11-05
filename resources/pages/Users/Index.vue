<template>
    <Head title="Usuarios" />

    <Layout>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 sm:text-4xl">Usuarios</h1>

                <Link
                    v-if="$page.props.auth?.user?.is_admin"
                    href="/users/create"
                    class="mt-2 inline-block text-sm text-blue-600 hover:underline"
                >
                    + Nuevo usuario
                </Link>
            </div>

            <input
                v-if="$page.props.auth?.user?.is_admin"
                v-model="search"
                type="text"
                placeholder="Buscar por nombre o correo electrónico..."
                class="w-full rounded-lg border px-3 py-2 sm:max-w-sm"
            />
        </div>

        <div class="space-y-3 lg:hidden">
            <article
                v-for="user in users.data"
                :key="user.id"
                class="rounded-xl bg-white p-4 shadow-sm"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <Link
                            :href="`/users/view/${user.id}`"
                            class="font-medium text-gray-800 hover:underline"
                        >
                            {{ user.name }}
                        </Link>
                        <p class="break-words text-sm text-gray-600">
                            {{ user.email }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">ID {{ user.id }}</p>
                    </div>

                    <span
                        class="shrink-0 rounded-full px-2 py-1 text-xs font-medium"
                        :class="roleClass(user)"
                    >
                        {{ roleLabel(user) }}
                    </span>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                    <Link
                        :href="`/users/view/${user.id}`"
                        class="text-blue-600 hover:underline"
                    >
                        Ver
                    </Link>

                    <Link
                        v-if="$page.props.auth?.user?.is_admin || user.id === $page.props.auth?.user?.id"
                        :href="`/users/edit/${user.id}?from=list`"
                        class="text-amber-600 hover:underline"
                    >
                        Editar
                    </Link>

                    <button
                        v-if="$page.props.auth?.user?.is_admin && user.id !== $page.props.auth.user.id"
                        type="button"
                        class="text-red-600 hover:underline"
                        @click="deleteUser(user)"
                    >
                        Eliminar
                    </button>
                </div>
            </article>

            <p
                v-if="users.data.length === 0"
                class="rounded-xl bg-white p-6 text-gray-500 shadow-sm"
            >
                No se encontraron usuarios.
            </p>
        </div>

        <div class="hidden overflow-x-auto rounded-xl bg-white shadow-sm lg:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                        >
                            ID
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                        >
                            Nombre
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                        >
                            Correo electrónico
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                        >
                            Rol
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500"
                        >
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    <tr v-for="user in users.data" :key="user.id">
                        <td class="px-6 py-4 text-gray-500">{{ user.id }}</td>

                        <td class="px-6 py-4">
                            <Link
                                :href="`/users/view/${user.id}`"
                                class="font-medium text-gray-800 hover:underline"
                            >
                                {{ user.name }}
                            </Link>
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ user.email }}
                        </td>

                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                :class="roleClass(user)"
                            >
                                {{ roleLabel(user) }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <Link
                                :href="`/users/view/${user.id}`"
                                class="text-blue-600 hover:underline"
                            >
                                Ver
                            </Link>

                            <Link
                                v-if="$page.props.auth?.user?.is_admin || user.id === $page.props.auth?.user?.id"
                                :href="`/users/edit/${user.id}?from=list`"
                                class="ml-3 text-amber-600 hover:underline"
                            >
                                Editar
                            </Link>

                            <button
                                v-if="$page.props.auth?.user?.is_admin && user.id !== $page.props.auth.user.id"
                                type="button"
                                class="ml-3 text-red-600 hover:underline"
                                @click="deleteUser(user)"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>

                    <tr v-if="users.data.length === 0">
                        <td colspan="5" class="px-6 py-4 text-gray-500">
                            No se encontraron usuarios.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="users.links" class="mt-6" />
    </Layout>
</template>

<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import debounce from "lodash/debounce";
import Pagination from "../../shared/Pagination.vue";

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: "" }),
    },
});

const search = ref(props.filters?.search ?? "");

const roleLabel = (user) => {
    if (user.is_admin && user.is_vendor) return "Admin / Vendedor";
    if (user.is_admin) return "Administrador";
    if (user.is_vendor) return "Vendedor";
    return "Usuario";
};

const roleClass = (user) => {
    if (user.is_admin && user.is_vendor) {
        return "bg-purple-100 text-purple-700";
    }
    if (user.is_admin) return "bg-indigo-100 text-indigo-700";
    if (user.is_vendor) return "bg-emerald-100 text-emerald-700";
    return "bg-gray-100 text-gray-600";
};

watch(
    search,
    debounce((value) => {
        router.get(
            "/users/list",
            { search: value },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            },
        );
    }, 300),
);

const deleteUser = (user) => {
    if (
        confirm(
            `¿Eliminar a "${user.name}"? También se borrarán sus cuadros, favoritos y comentarios.`,
        )
    ) {
        router.post(`/users/delete/${user.id}`);
    }
};
</script>
