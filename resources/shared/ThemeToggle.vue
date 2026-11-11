<template>
    <button
        type="button"
        :class="[
            'inline-flex items-center border border-gray-200 bg-white text-gray-700 shadow-sm transition duration-200 ease-out hover:bg-gray-50 active:scale-95 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700 dark:active:bg-gray-600 dark:focus:ring-offset-gray-900',
            variant === 'menu'
                ? 'w-full justify-start gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium'
                : 'h-10 w-10 justify-center rounded-full',
        ]"
        :aria-label="isDark ? 'Activar modo claro' : 'Activar modo oscuro'"
        :aria-pressed="isDark"
        :title="isDark ? 'Activar modo claro' : 'Activar modo oscuro'"
        @click="toggleTheme"
    >
        <HeroIcon :name="isDark ? 'sun' : 'moon'" class="h-5 w-5 shrink-0" />

        <span v-if="variant === 'menu'">
            {{ isDark ? "Modo claro" : "Modo oscuro" }}
        </span>
    </button>
</template>

<script setup>
import axios from "axios";
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import { applyTheme, isDark, resolveTheme, saveTheme } from "../js/theme";
import HeroIcon from "./HeroIcon.vue";

defineProps({
    variant: {
        type: String,
        default: "icon",
    },
});

const page = usePage();

onMounted(() => {
    const user = page.props.auth?.user;

    if (user) {
        applyTheme(resolveTheme(user.theme, user.id));
    }
});

const toggleTheme = () => {
    const theme = isDark.value ? "light" : "dark";
    const userId = page.props.auth?.user?.id ?? null;

    applyTheme(theme);
    saveTheme(userId, theme);

    if (userId) {
        axios.post("/mi-cuenta/tema", { theme }).catch(() => {
        });
    }
};
</script>
