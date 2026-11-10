<template>
    <Link
        href="/notificaciones"
        class="relative inline-flex h-10 w-10 touch-manipulation items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm transition duration-200 ease-out hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 active:scale-90 active:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:border-indigo-400/50 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-200 dark:active:bg-indigo-500/30 dark:focus:ring-offset-gray-900"
        :class="{ 'text-indigo-700 dark:text-indigo-300': unread > 0 }"
        aria-label="Notificaciones"
    >
        <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            />
        </svg>

        <span
            v-if="unread > 0"
            class="absolute -right-1 -top-1 inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold leading-5 text-white"
        >
            {{ unread > 99 ? "99+" : unread }}
        </span>
    </Link>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const unread = computed(() => Number(page.props.unreadNotifications ?? 0));
</script>
