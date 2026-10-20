<template>
    <Head>
        <title>Users</title>
    </Head>

    <Layout>
        <div class="flex justify-between mb-6">
            <h1 class="text-3xl">👥 Users</h1>

            <input
                v-model="search"
                type="text"
                placeholder="Search..."
                class="border px-2 rounded-lg"
            />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Nombre
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="user in users.data" :key="user.id">
                        <td class="px-6 py-4">
                            {{ user.name }}
                        </td>

                        <td class="px-6 py-4 text-right">
                            <Link
                                :href="`/users/${user.id}/edit`"
                                class="text-indigo-600 hover:text-indigo-900"
                            >
                                Edit
                            </Link>
                        </td>
                    </tr>

                    <tr v-if="users.data.length === 0">
                        <td colspan="2" class="px-6 py-4 text-gray-500">
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
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '../Shared/Pagination.vue';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
});

const search = ref(props.filters?.search ?? '');

watch(search, (value) => {
    router.get(
        '/users',
        { search: value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        }
    );
});
</script>