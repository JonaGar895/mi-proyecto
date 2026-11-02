<template>
    <Head title="Iniciar sesión" />

    <main class="relative grid min-h-screen place-items-center bg-gray-50 px-4 py-10">
        <div class="absolute right-4 top-4 sm:right-6 sm:top-6">
            <ThemeToggle />
        </div>

        <section class="mx-auto w-full max-w-md rounded-xl border bg-white p-6 sm:p-8">
            <h1 class="mb-6 text-2xl font-bold sm:text-3xl">Iniciar sesión</h1>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label for="email" class="block mb-2 uppercase font-bold text-xs text-gray-700">
                        Correo electrónico
                    </label>

                    <input
                        v-model="form.email"
                        type="email"
                        id="email"
                        autocomplete="email"
                        class="border p-2 w-full rounded"
                        required
                    />

                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div class="mb-6">
                    <label for="password" class="block mb-2 uppercase font-bold text-xs text-gray-700">
                        Contraseña
                    </label>

                    <input
                        v-model="form.password"
                        type="password"
                        id="password"
                        autocomplete="current-password"
                        class="border p-2 w-full rounded"
                        required
                    />

                    <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">
                        {{ form.errors.password }}
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 disabled:opacity-50"
                >
                    {{ form.processing ? 'Entrando...' : 'Iniciar sesión' }}
                </button>
            </form>
        </section>
    </main>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ThemeToggle from '../../shared/ThemeToggle.vue';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>