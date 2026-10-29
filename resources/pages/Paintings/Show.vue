<template>
    <Head :title="painting.title" />

    <Layout>
        <div class="grid gap-8 lg:grid-cols-[1.2fr_1fr]">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div
                    class="flex h-80 items-center justify-center bg-gray-100 text-8xl"
                >
                    <img
                        v-if="painting.image_url"
                        :src="painting.image_url"
                        :alt="painting.title"
                        class="h-full w-full object-cover"
                    />
                    <span v-else>{{ painting.emoji }}</span>
                </div>
            </div>

            <div>
                <Link href="/cuadros" class="text-sm text-blue-600 hover:underline">
                    ← Volver a la galería
                </Link>

                <div class="mt-3 flex items-start justify-between gap-4">
                    <h1 class="text-3xl font-bold text-gray-800">
                        {{ painting.title }}
                    </h1>

                    <button
                        type="button"
                        class="rounded-full px-3 py-1 text-2xl leading-none"
                        :class="
                            painting.is_favorited
                                ? 'text-red-500'
                                : 'text-gray-400 hover:text-red-500'
                        "
                        :title="
                            painting.is_favorited
                                ? 'Quitar de favoritos'
                                : 'Añadir a favoritos'
                        "
                        @click="toggleFavorite"
                    >
                        {{ painting.is_favorited ? "♥" : "♡" }}
                    </button>
                </div>

                <p class="mt-1 text-gray-600">
                    {{ painting.artist ? painting.artist.name : "Anónimo" }}
                </p>

                <div class="mt-4 flex flex-wrap gap-2 text-sm text-gray-600">
                    <span class="rounded-full bg-gray-100 px-3 py-1">
                        {{ painting.category ? painting.category.name : "Sin categoría" }}
                    </span>
                    <span v-if="painting.year" class="rounded-full bg-gray-100 px-3 py-1">
                        {{ painting.year }}
                    </span>
                    <span
                        v-if="painting.technique"
                        class="rounded-full bg-gray-100 px-3 py-1"
                    >
                        {{ painting.technique }}
                    </span>
                    <span v-if="painting.price" class="rounded-full bg-gray-100 px-3 py-1">
                        {{ formatPrice(painting.price) }}
                    </span>
                    <span class="rounded-full bg-gray-100 px-3 py-1">
                        Estado: {{ painting.status === 'sold' ? 'Vendida' : painting.status === 'reserved' ? 'Reservada' : 'Disponible' }}
                    </span>
                </div>

                <div class="mt-4 rounded-lg bg-blue-50 p-3 text-sm text-blue-800">
                    <p class="font-medium">Tu saldo</p>
                    <p class="mt-1 text-lg font-semibold">
                        {{ auth?.user?.balance ? formatPrice(auth.user.balance) : formatPrice(0) }}
                    </p>
                    <p v-if="painting.price" class="mt-1 text-xs text-blue-700">
                        Precio de la obra: {{ formatPrice(painting.price) }}
                    </p>
                </div>

                <div class="mt-4">
                    <button
                        v-if="canShowBuyButton"
                        type="button"
                        class="w-full rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing || !auth.can_buy"
                        @click="buyPainting"
                    >
                        {{ form.processing ? "Enviando solicitud..." : "Comprar obra" }}
                    </button>
                    <p v-if="auth.buy_reason && !canShowBuyButton && !hasInquired" class="mt-2 text-sm text-gray-600">
                        {{ auth.buy_reason }}
                    </p>
                    <p v-if="hasInquired" class="mt-2 text-sm text-amber-700">
                        Ya has enviado una solicitud de compra. Pendiente de aprobación por un administrador.
                    </p>
                    <p v-if="painting.status === 'sold'" class="mt-2 text-sm text-red-600">
                        Esta obra ya ha sido vendida.
                    </p>
                </div>

                <p class="mt-5 whitespace-pre-line text-gray-700">
                    {{ painting.description || "Sin descripción." }}
                </p>

                <div class="mt-4 flex gap-4 text-sm text-gray-500">
                    <span>♥ {{ painting.favorites_count }} favoritos</span>
                    <span>💬 {{ painting.comments_count }} comentarios</span>
                </div>

                <div v-if="painting.exhibitions.length" class="mt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Exposiciones
                    </h2>
                    <ul class="mt-2 space-y-2">
                        <li
                            v-for="exhibition in painting.exhibitions"
                            :key="exhibition.id"
                            class="rounded-lg bg-white p-3 text-sm shadow-sm"
                        >
                            <p class="font-medium text-gray-800">
                                {{ exhibition.title }}
                            </p>
                            <p class="text-gray-500">
                                <span v-if="exhibition.starts_at">
                                    Desde {{ exhibition.starts_at }}
                                </span>
                                <span v-if="exhibition.ends_at">
                                    · hasta {{ exhibition.ends_at }}
                                </span>
                            </p>
                        </li>
                    </ul>
                </div>

                <button
                    v-if="painting.can_delete"
                    type="button"
                    class="mt-6 text-sm text-red-600 hover:underline"
                    @click="destroy"
                >
                    Eliminar obra
                </button>
            </div>
        </div>

        <section class="mt-12">
            <h2 class="text-2xl font-bold text-gray-800">
                Comentarios ({{ painting.comments_count }})
            </h2>

            <form
                class="mt-4 rounded-xl bg-white p-4 shadow-sm"
                @submit.prevent="submitComment"
            >
                <textarea
                    v-model="commentForm.body"
                    rows="3"
                    placeholder="Escribe un comentario sobre esta obra..."
                    class="w-full rounded-lg border px-3 py-2"
                />
                <p
                    v-if="commentForm.errors.body"
                    class="mt-1 text-xs text-red-600"
                >
                    {{ commentForm.errors.body }}
                </p>

                <button
                    type="submit"
                    class="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    :disabled="commentForm.processing"
                >
                    {{ commentForm.processing ? "Enviando..." : "Comentar" }}
                </button>
            </form>

            <div class="mt-6 space-y-3">
                <div
                    v-for="comment in comments"
                    :key="comment.id"
                    class="rounded-xl bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-800">
                            {{ comment.user.name }}
                        </span>
                        <span class="text-gray-500">{{ comment.created_at }}</span>
                    </div>
                    <p class="mt-2 text-gray-700">{{ comment.body }}</p>
                </div>

                <p v-if="!comments.length" class="text-gray-500">
                    Todavía no hay comentarios. Sé el primero.
                </p>
            </div>
        </section>
    </Layout>
</template>

<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    painting: {
        type: Object,
        required: true,
    },
    comments: {
        type: Array,
        default: () => [],
    },
    hasInquired: {
        type: Boolean,
        default: false,
    },
    auth: {
        type: Object,
        default: () => ({
            user: null,
            can_buy: false,
            buy_reason: null,
        }),
    },
});

const commentForm = useForm({
    body: "",
});

const form = useForm({});

const canShowBuyButton = computed(() => {
    if (props.painting.status === 'sold') {
        return false;
    }
    if (props.hasInquired) {
        return false;
    }
    if (props.painting.is_owner) {
        return false;
    }
    return true;
});

const toggleFavorite = () => {
    router.post(
        `/cuadros/${props.painting.id}/favorite`,
        {},
        { preserveScroll: true },
    );
};

const submitComment = () => {
    commentForm.post(`/cuadros/${props.painting.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
};

const destroy = () => {
    if (confirm("¿Seguro que quieres eliminar esta obra?")) {
        router.delete(`/cuadros/${props.painting.id}`);
    }
};

const buyPainting = () => {
    if (!props.auth?.can_buy) {
        alert(props.auth?.buy_reason || 'No puedes comprar esta obra.');
        return;
    }
    if (confirm("¿Enviar solicitud de compra para esta obra? Quedará pendiente de aprobación por un administrador.")) {
        form.post(`/cuadros/${props.painting.id}/inquiries`, {
            preserveScroll: true,
        });
    }
};

const formatPrice = (price) =>
    Number(price).toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
    });
</script>
