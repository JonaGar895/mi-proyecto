<template>
    <Head title="Nueva obra" />

    <Layout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-3xl font-bold text-gray-800 sm:text-4xl">Nueva obra</h1>
            <p class="mt-2 text-gray-600">
                Añade un cuadro a la galería. Podrás editarlo más adelante.
            </p>

            <form class="mt-8 space-y-5" @submit.prevent="submit">
                <div>
                    <label for="title" class="mb-1 block text-sm font-medium text-gray-700">
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
                        for="description"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Descripción
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="w-full rounded-lg border px-3 py-2"
                    />
                    <p
                        v-if="form.errors.description"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.description }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        No se permiten obras con el mismo título, descripción o imagen.
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
                            for="year"
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Año
                        </label>
                        <input
                            id="year"
                            v-model="form.year"
                            type="number"
                            min="1000"
                            :max="new Date().getFullYear()"
                            class="w-full rounded-lg border px-3 py-2"
                        />
                        <p v-if="form.errors.year" class="mt-1 text-xs text-red-600">
                            {{ form.errors.year }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="technique"
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Técnica
                        </label>
                        <input
                            id="technique"
                            v-model="form.technique"
                            type="text"
                            placeholder="Óleo sobre lienzo"
                            class="w-full rounded-lg border px-3 py-2"
                        />
                        <p
                            v-if="form.errors.technique"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.technique }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <label
                            for="image"
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Imagen de la obra
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

                    <div>
                        <label
                            for="price"
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Precio (€)
                        </label>
                        <input
                            id="price"
                            v-model="form.price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border px-3 py-2"
                        />
                        <p v-if="form.errors.price" class="mt-1 text-xs text-red-600">
                            {{ form.errors.price }}
                        </p>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input v-model="form.is_public" type="checkbox" />
                    Visible en la galería pública
                </label>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50 sm:w-auto"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Guardando..." : "Publicar obra" }}
                    </button>

                    <Link href="/cuadros" class="text-center text-gray-600 hover:underline sm:text-left">
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
});

const form = useForm({
    title: "",
    description: "",
    category_id: "",
    year: "",
    technique: "",
    image: null,
    price: "",
    is_public: true,
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
    form.post("/cuadros", {
        forceFormData: true,
    });
};

onBeforeUnmount(() => {
    if (preview.value) {
        URL.revokeObjectURL(preview.value);
    }
});
</script>
