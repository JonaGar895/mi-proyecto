<template>
    <Head title="Nueva publicación" />

    <Layout>
        <div class="mx-auto max-w-3xl">
            <Link
                href="/publicaciones"
                class="mb-4 inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                ← Volver a publicaciones
            </Link>

            <h1 class="text-4xl font-bold text-gray-800">Nueva publicación</h1>
            <p class="mt-2 text-gray-600">
                Comparte una noticia, un artículo o una reflexión sobre la
                galería.
            </p>

            <form class="mt-8 space-y-5" @submit.prevent="submit">
                <div>
                    <label
                        for="title"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Título *
                    </label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-lg border px-3 py-2"
                        required
                    />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">
                        {{ form.errors.title }}
                    </p>
                </div>

                <div>
                    <label
                        for="summary"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Resumen
                    </label>
                    <textarea
                        id="summary"
                        v-model="form.summary"
                        rows="2"
                        maxlength="500"
                        placeholder="Una frase que resuma la publicación."
                        class="w-full rounded-lg border px-3 py-2"
                    />
                    <p v-if="form.errors.summary" class="mt-1 text-xs text-red-600">
                        {{ form.errors.summary }}
                    </p>
                </div>

                <div>
                    <label
                        for="content"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Contenido *
                    </label>
                    <textarea
                        id="content"
                        v-model="form.content"
                        rows="10"
                        class="w-full rounded-lg border px-3 py-2"
                        required
                    />
                    <p v-if="form.errors.content" class="mt-1 text-xs text-red-600">
                        {{ form.errors.content }}
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label
                            for="category_id"
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Categoría
                        </label>
                        <select
                            id="category_id"
                            v-model="form.category_id"
                            class="w-full rounded-lg border px-3 py-2"
                        >
                            <option value="">Sin categoría</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.category_id"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.category_id }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="painting_id"
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Obra relacionada
                        </label>
                        <select
                            id="painting_id"
                            v-model="form.painting_id"
                            class="w-full rounded-lg border px-3 py-2"
                        >
                            <option value="">Ninguna obra</option>
                            <option
                                v-for="painting in paintings"
                                :key="painting.id"
                                :value="painting.id"
                            >
                                {{ painting.title }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.painting_id"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.painting_id }}
                        </p>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input v-model="form.is_published" type="checkbox" />
                    Publicar ahora (si lo desmarcas se guarda como borrador)
                </label>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Publicando..." : "Publicar" }}
                    </button>

                    <Link
                        href="/publicaciones"
                        class="text-gray-600 hover:underline"
                    >
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    paintings: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    title: "",
    summary: "",
    content: "",
    category_id: "",
    painting_id: "",
    is_published: true,
});

const submit = () => {
    form.post("/publicaciones");
};
</script>
