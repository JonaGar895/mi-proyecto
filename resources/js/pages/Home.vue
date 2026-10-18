<template>
    <Layout>
        <h1 class="text-4xl font-bold text-gray-800">
            🎨 Galería de Cuadros
        </h1>

        <p class="mt-2 text-gray-600">
            Bienvenido a nuestra galería.
        </p>
    </Layout>
</template>

<script>
import Layout from '../shared/Layout.vue';

export default {
    components: {
        Layout,
    },

    props: {
        username: String,
        frameworks: Array,
    },
};
</script>