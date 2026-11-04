<template>
    <button
        type="button"
        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-xl shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900"
        :aria-label="isDark ? 'Activar modo claro' : 'Activar modo oscuro'"
        :aria-pressed="isDark"
        :title="isDark ? 'Activar modo claro' : 'Activar modo oscuro'"
        @click="toggleTheme"
    >
        <span aria-hidden="true">{{ isDark ? "☀️" : "🌙" }}</span>
    </button>
</template>

<script setup>
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import { applyTheme, isDark, saveTheme } from "../js/theme";

const page = usePage();

const toggleTheme = () => {
    const theme = isDark.value ? "light" : "dark";
    const userId = page.props.auth?.user?.id ?? null;

    applyTheme(theme);
    saveTheme(userId, theme);

    if (userId) {
        // Persistimos la preferencia para que se mantenga en otros navegadores.
        axios.post("/mi-cuenta/tema", { theme }).catch(() => {
            // Si falla la petición, el tema elegido se mantiene en este navegador.
        });
    }
};
</script>
