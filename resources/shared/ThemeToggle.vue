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
import { ref } from "vue";

const isDark = ref(
    typeof document !== "undefined" &&
        document.documentElement.classList.contains("dark"),
);

const toggleTheme = () => {
    isDark.value = !isDark.value;

    if (isDark.value) {
        document.documentElement.classList.add("dark");
    } else {
        document.documentElement.classList.remove("dark");
    }

    localStorage.setItem("theme", isDark.value ? "dark" : "light");
};
</script>
