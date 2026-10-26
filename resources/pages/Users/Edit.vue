<template>
    <Head title="Editar usuario" />

    <Layout>
        <div class="mx-auto max-w-2xl">
            <Link
                :href="backHref"
                class="mb-4 inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                ← Volver
            </Link>

            <h1 class="text-4xl font-bold text-gray-800">Editar usuario</h1>
            <p class="mt-2 text-gray-600">
                Actualiza los datos de {{ user.name }}.
            </p>

            <form class="mt-8 space-y-5" @submit.prevent="submit">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700">
                        Nombre
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-lg border px-3 py-2"
                        required
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">
                        Correo electrónico
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="w-full rounded-lg border px-3 py-2"
                        required
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label
                        for="password"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Nueva contraseña
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="Déjala vacía para no cambiarla"
                        class="w-full rounded-lg border px-3 py-2"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div>
                    <label
                        for="password_confirmation"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Confirmar contraseña
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full rounded-lg border px-3 py-2"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Guardando..." : "Guardar cambios" }}
                    </button>

                    <Link
                        :href="backHref"
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
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    backUrl: {
        type: String,
        default: "",
    },
});

const backHref = computed(
    () => props.backUrl || `/users/view/${props.user.id}`,
);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(`/users/edit/${props.user.id}`);
};
</script>
