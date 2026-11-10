<template>
    <Head title="Nueva exposición" />

    <Layout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-3xl font-bold text-gray-800 sm:text-4xl">Nueva exposición</h1>
            <p class="mt-2 text-gray-600">
                Organiza una colección de obras bajo un mismo tema y periodo.
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
                    <label for="description" class="mb-1 block text-sm font-medium text-gray-700">
                        Descripción
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="w-full rounded-lg border px-3 py-2"
                    />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">
                        {{ form.errors.description }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        No se permiten exposiciones con el mismo título o descripción.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="starts_at" class="mb-1 block text-sm font-medium text-gray-700">
                            Fecha de inicio
                        </label>
                        <input
                            id="starts_at"
                            v-model="form.starts_at"
                            type="date"
                            class="w-full rounded-lg border px-3 py-2"
                        />
                        <p v-if="form.errors.starts_at" class="mt-1 text-xs text-red-600">
                            {{ form.errors.starts_at }}
                        </p>
                    </div>

                    <div>
                        <label for="ends_at" class="mb-1 block text-sm font-medium text-gray-700">
                            Fecha de fin
                        </label>
                        <input
                            id="ends_at"
                            v-model="form.ends_at"
                            type="date"
                            class="w-full rounded-lg border px-3 py-2"
                        />
                        <p v-if="form.errors.ends_at" class="mt-1 text-xs text-red-600">
                            {{ form.errors.ends_at }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Obras incluidas ({{ selectedPaintings.length }})
                    </label>

                    <input
                        v-model="paintingSearch"
                        type="text"
                        placeholder="Filtrar obras por título o artista..."
                        class="mb-3 w-full rounded-lg border px-3 py-2 text-sm"
                    />

                    <div
                        v-if="filteredPaintings.length"
                        class="grid max-h-80 grid-cols-2 gap-2 overflow-y-auto rounded-lg border p-3 sm:grid-cols-3"
                    >
                        <label
                            v-for="painting in filteredPaintings"
                            :key="painting.id"
                            class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-sm transition hover:bg-blue-50"
                            :class="{ 'bg-blue-50 border-blue-400': isSelected(painting.id) }"
                        >
                            <input
                                type="checkbox"
                                :value="painting.id"
                                :checked="isSelected(painting.id)"
                                class="shrink-0"
                                @change="togglePainting(painting.id)"
                            />

                            <span class="min-w-0 truncate">
                                {{ painting.emoji }} {{ painting.title }}
                                <span class="block text-xs text-gray-500">{{ painting.artist }}</span>
                            </span>
                        </label>
                    </div>

                    <p v-else class="text-sm text-gray-500">
                        No hay obras disponibles para mostrar.
                    </p>

                    <p v-if="form.errors.paintings" class="mt-1 text-xs text-red-600">
                        {{ form.errors.paintings }}
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50 sm:w-auto"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Guardando..." : "Crear exposición" }}
                    </button>

                    <Link href="/exposiciones" class="text-center text-gray-600 hover:underline sm:text-left">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    paintings: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    title: "",
    description: "",
    starts_at: "",
    ends_at: "",
    paintings: [],
});

const paintingSearch = ref("");

const selectedPaintings = computed(() => form.paintings);

const filteredPaintings = computed(() => {
    const term = paintingSearch.value.trim().toLowerCase();

    if (!term) {
        return props.paintings;
    }

    return props.paintings.filter(
        (painting) =>
            painting.title.toLowerCase().includes(term) ||
            (painting.artist || "").toLowerCase().includes(term),
    );
});

const isSelected = (id) => form.paintings.includes(id);

const togglePainting = (id) => {
    if (isSelected(id)) {
        form.paintings = form.paintings.filter((item) => item !== id);
    } else {
        form.paintings = [...form.paintings, id];
    }
};

const submit = () => {
    form.post("/exposiciones");
};
</script>
