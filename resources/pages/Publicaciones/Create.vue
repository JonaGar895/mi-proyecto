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

            <h1 class="text-3xl font-bold text-gray-800 sm:text-4xl">Nueva publicación</h1>
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
                    <p class="mt-1 text-xs text-gray-500">
                        No se permiten publicaciones con el mismo título, resumen, contenido o imagen.
                    </p>
                </div>

                <div>
                    <label
                        for="image"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Imagen de la publicación
                    </label>
                    <input
                        id="image"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        class="w-full cursor-pointer rounded-lg border px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-blue-600 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-blue-700"
                        @change="onImageChange"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        JPG, PNG, WEBP o GIF. Tamaño máximo: 5 MB.
                    </p>
                    <p v-if="form.errors.image" class="mt-1 text-xs text-red-600">
                        {{ form.errors.image }}
                    </p>

                    <img
                        v-if="preview"
                        :src="preview"
                        alt="Vista previa de la imagen"
                        class="mt-3 block h-auto max-h-72 w-auto max-w-full rounded-lg border border-gray-200 dark:border-gray-700"
                    />
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
                                {{ painting.emoji }} {{ painting.title }}
                                <template v-if="painting.user && painting.user.id !== $page.props.auth.user.id">
                                    — {{ painting.user.name }}
                                </template>
                            </option>
                        </select>
                        <p
                            v-if="form.errors.painting_id"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.painting_id }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Puedes enlazar tus propias obras o cualquier obra pública de otros usuarios.
                        </p>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input v-model="form.is_published" type="checkbox" />
                    Publicar ahora (si lo desmarcas se guarda como borrador)
                </label>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50 sm:w-auto"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Publicando..." : "Publicar" }}
                    </button>

                    <Link
                        href="/publicaciones"
                        class="text-center text-gray-600 hover:underline sm:text-left"
                    >
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { onBeforeUnmount, ref } from "vue";
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
    image: null,
    is_published: true,
});

const preview = ref(null);

function onImageChange(event) {
    const file = event.target.files?.[0] ?? null;

    if (preview.value) {
        URL.revokeObjectURL(preview.value);
        preview.value = null;
    }

    form.image = file;

    if (file) {
        preview.value = URL.createObjectURL(file);
    }
}

const submit = () => {
    form.post("/publicaciones", {
        forceFormData: true,
    });
};

onBeforeUnmount(() => {
    if (preview.value) {
        URL.revokeObjectURL(preview.value);
    }
});
</script>
