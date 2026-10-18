<template>
    <Layout>
        <h1 class="text-4xl font-bold text-gray-800">
            🖼️ Cuadros
        </h1>

        <p class="mt-2 text-gray-600">
            Explora nuestra galería de obras.
        </p>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="painting in paintings"
                :key="painting.id"
                class="overflow-hidden rounded-lg bg-white shadow-sm"
            >
                <div class="flex h-48 items-center justify-center bg-gray-100 text-6xl">
                    {{ painting.emoji }}
                </div>

                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800">
                        {{ painting.title }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ painting.author }}
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
import Layout from '../shared/Layout.vue';

export default {
    components: {
        Layout,
    },

    props: {
        paintings: {
            type: Array,
            default: () => [
                { id: 1, title: 'Obra 1', author: 'Autor desconocido', emoji: '🌅' },
                { id: 2, title: 'Obra 2', author: 'Autor desconocido', emoji: '🌄' },
                { id: 3, title: 'Obra 3', author: 'Autor desconocido', emoji: '🌇' },
            ],
        },
    },
};
</script>
