<template>
    <Head title="Notificaciones" />

    <Layout>
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 sm:text-4xl">🔔 Notificaciones</h1>
                <p class="mt-2 text-gray-600">
                    Comentarios, favoritos, respuestas y actualizaciones de tus solicitudes.
                </p>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <button
                    v-if="notifications.unread_count"
                    type="button"
                    class="w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700 sm:w-auto"
                    :disabled="form.processing"
                    @click="markAllRead"
                >
                    Marcar todas como leídas ({{ notifications.unread_count }})
                </button>

                <button
                    v-if="notifications.data.length"
                    type="button"
                    class="w-full rounded-lg border border-red-200 bg-white px-4 py-2 text-center text-red-600 hover:bg-red-50 sm:w-auto dark:border-red-900 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-950/40"
                    :disabled="form.processing"
                    @click="deleteAll"
                >
                    Borrar todas
                </button>
            </div>
        </div>

        <div class="mt-8 space-y-3">
            <div
                v-for="notification in notifications.data"
                :key="notification.id"
                class="rounded-xl bg-white p-4 shadow-sm transition sm:p-5"
                :class="[
                    { 'ring-2 ring-blue-500 ring-offset-2': notification.unread },
                    notification.url ? 'cursor-pointer hover:shadow-md' : '',
                ]"
                :role="notification.url ? 'link' : undefined"
                :tabindex="notification.url ? 0 : undefined"
                @click="open(notification)"
                @keydown.enter="open(notification)"
            >
                <div class="flex items-start gap-3">
                    <span class="text-2xl leading-none">{{ notification.icon }}</span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-semibold text-gray-800">
                                {{ notification.title }}
                                <span
                                    v-if="notification.unread"
                                    class="ml-1 inline-block h-2 w-2 rounded-full bg-blue-600 align-middle"
                                    aria-hidden="true"
                                ></span>
                            </p>

                            <span class="text-xs text-gray-400">
                                {{ notification.created_at }}
                            </span>
                        </div>

                        <p class="mt-1 break-words text-sm text-gray-600">
                            {{ notification.message }}
                        </p>

                        <div class="mt-2 flex flex-wrap items-center gap-4 text-sm">
                            <button
                                v-if="notification.unread"
                                type="button"
                                class="text-gray-500 hover:underline"
                                @click.stop="markRead(notification)"
                            >
                                Marcar como leída
                            </button>

                            <button
                                type="button"
                                class="inline-flex items-center gap-1 text-gray-400 hover:text-red-600 dark:text-gray-500 dark:hover:text-red-400"
                                @click.stop="deleteNotification(notification)"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                                Borrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <p
                v-if="!notifications.data.length"
                class="rounded-xl bg-white p-6 text-gray-500 shadow-sm sm:p-8"
            >
                No tienes notificaciones por ahora.
            </p>
        </div>

        <Pagination
            v-if="notifications.has_pages"
            :links="notifications.links"
            class="mt-8"
        />
    </Layout>
</template>

<script setup>
import { Head, router, useForm } from "@inertiajs/vue3";
import Pagination from "../../shared/Pagination.vue";

defineProps({
    notifications: {
        type: Object,
        required: true,
    },
});

const form = useForm({});

const open = (notification) => {
    if (!notification.url) {
        return;
    }

    router.get(notification.url);
};

const markRead = (notification) => {
    router.post(`/notificaciones/${notification.id}/leer`, {}, {
        preserveScroll: true,
    });
};

const markAllRead = () => {
    form.post("/notificaciones/leer-todas", {
        preserveScroll: true,
    });
};

const deleteNotification = (notification) => {
    if (!window.confirm("¿Eliminar esta notificación?")) {
        return;
    }

    router.delete(`/notificaciones/${notification.id}`, {
        preserveScroll: true,
    });
};

const deleteAll = () => {
    if (!window.confirm("¿Eliminar todas las notificaciones? Esta acción no se puede deshacer.")) {
        return;
    }

    form.post("/notificaciones/borrar-todas", {
        preserveScroll: true,
    });
};
</script>
