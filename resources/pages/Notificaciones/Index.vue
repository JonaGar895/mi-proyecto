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

            <button
                v-if="notifications.unread_count"
                type="button"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700 sm:w-auto"
                :disabled="form.processing"
                @click="markAllRead"
            >
                Marcar todas como leídas ({{ notifications.unread_count }})
            </button>
        </div>

        <div class="mt-8 space-y-3">
            <div
                v-for="notification in notifications.data"
                :key="notification.id"
                class="rounded-xl bg-white p-4 shadow-sm transition sm:p-5"
                :class="{ 'ring-2 ring-blue-500 ring-offset-2': notification.unread }"
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
                            <Link
                                v-if="notification.url"
                                :href="notification.url"
                                class="text-blue-600 hover:underline"
                            >
                                Ver
                            </Link>

                            <button
                                v-if="notification.unread"
                                type="button"
                                class="text-gray-500 hover:underline"
                                @click="markRead(notification)"
                            >
                                Marcar como leída
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
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import Pagination from "../../shared/Pagination.vue";

defineProps({
    notifications: {
        type: Object,
        required: true,
    },
});

const form = useForm({});

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
</script>
