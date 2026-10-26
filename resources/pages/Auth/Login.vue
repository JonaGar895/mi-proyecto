<template>
    <Head title="Iniciar sesión" />

    <main class="grid place-items-center min-h-screen bg-gray-50">
        <section class="bg-white p-8 rounded-xl max-w-md mx-auto border w-full">
            <h1 class="text-3xl font-bold mb-6">Iniciar sesión</h1>

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
                    class="bg-blue-500 text-white rounded py-2 px-4 hover:bg-blue-600 disabled:opacity-50"
                >
                    {{ form.processing ? 'Entrando...' : 'Iniciar sesión' }}
                </button>
            </form>
        </section>
    </main>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';

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