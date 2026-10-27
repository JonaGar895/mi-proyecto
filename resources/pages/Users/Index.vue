<template>
    <Head title="Usuarios" />

    <Layout>
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-800">Usuarios</h1>

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

        <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
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

                        <td class="px-6 py-4 text-right whitespace-nowrap">
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
                        <td colspan="4" class="px-6 py-4 text-gray-500">
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
