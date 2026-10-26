<template>
    <Head title="Nueva obra" />

    <Layout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-4xl font-bold text-gray-800">Nueva obra</h1>
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

                    <div>
                        <label
                            for="emoji"
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Emoji
                        </label>
                        <input
                            id="emoji"
                            v-model="form.emoji"
                            type="text"
                            maxlength="16"
                            class="w-full rounded-lg border px-3 py-2"
                        />
                        <p v-if="form.errors.emoji" class="mt-1 text-xs text-red-600">
                            {{ form.errors.emoji }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="image_url"
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            URL de la imagen
                        </label>
                        <input
                            id="image_url"
                            v-model="form.image_url"
                            type="url"
                            placeholder="https://..."
                            class="w-full rounded-lg border px-3 py-2"
                        />
                        <p
                            v-if="form.errors.image_url"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.image_url }}
                        </p>
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

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Guardando..." : "Publicar obra" }}
                    </button>

                    <Link href="/cuadros" class="text-gray-600 hover:underline">
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
});

const form = useForm({
    title: "",
    description: "",
    category_id: "",
    year: "",
    technique: "",
    emoji: "🖼️",
    image_url: "",
    price: "",
    is_public: true,
});

const submit = () => {
    form.post("/cuadros");
};
</script>
