<template>
    <Head title="Crear usuario" />

    <Layout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-4xl font-bold text-gray-800">Crear usuario</h1>
            <p class="mt-2 text-gray-600">
                Añade un nuevo usuario a la aplicación.
            </p>

            <form class="mt-8 space-y-5" @submit.prevent="submit">
                <div>
                    <label
                        for="name"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
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
                    <label
                        for="email"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
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
                        Contraseña
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        minlength="8"
                        class="w-full rounded-lg border px-3 py-2"
                        required
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.password }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Mínimo 8 caracteres.
                    </p>
                </div>

                <div v-if="$page.props.auth?.user?.is_admin" class="flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <input id="is_admin" v-model="form.is_admin" type="checkbox" class="rounded border-gray-300" />
                        <label for="is_admin" class="text-sm font-medium text-gray-700">Administrador</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="is_vendor" v-model="form.is_vendor" type="checkbox" class="rounded border-gray-300" />
                        <label for="is_vendor" class="text-sm font-medium text-gray-700">Vendedor</label>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Creando..." : "Crear usuario" }}
                    </button>

                    <Link href="/users/list" class="text-gray-600 hover:underline">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    email: "",
    password: "",
    is_admin: false,
    is_vendor: false,
});

const submit = () => {
    form.post("/users/create");
};
</script>
