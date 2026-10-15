<template>
    <Layout>
        <h1 class="text-4xl font-bold text-gray-800">
            👥 Users
        </h1>

        <div class="mt-10 rounded-lg bg-white p-6 shadow-sm">
            <p class="text-gray-600">
                La hora actual es: {{ time }}
            </p>

            <Link
                href="/users"
                class="mt-4 inline-block text-blue-600 hover:text-blue-800"
                preserve-scroll
            >
                Actualizar hora
            </Link>
        </div>
    </Layout>
</template>

<script>
import Layout from '../shared/Layout.vue';
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        Layout,
        Link,
    },

    props: {
        name: String,
        frameworks: Array,
        time: String,
    },
};
</script>