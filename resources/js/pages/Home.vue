<template>
        <h1 class="text-2xl font-bold text-gray-800">
            🎨 Galería de Cuadros
        </h1>

        <p class="mt-2 text-gray-600">
            Bienvenido a nuestra galería.
        </p>
</template>

<script>
import Layout from '../shared/Layout.vue';

export default {

    Layout:{ Layout

    },

    components: {
        Layout,
    },
    

    props: {
        username: String,
        frameworks: Array,
    },
};
</script>