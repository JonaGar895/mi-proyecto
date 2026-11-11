<template>
    <Head :title="painting.title" />

    <Layout>
        <div class="grid gap-6 lg:grid-cols-[1.2fr_1fr] lg:gap-8">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <!-- El recuadro se adapta a la proporcion de la imagen: se
                     muestra completa, con un alto maximo para las muy
                     verticales. -->
                <div class="flex items-center justify-center bg-gray-100 text-7xl sm:text-8xl">
                    <img
                        v-if="painting.image_url"
                        :src="painting.image_url"
                        :alt="painting.title"
                        class="max-h-[75vh] w-full object-contain"
                    />
                    <span
                        v-else
                        class="flex h-64 w-full items-center justify-center sm:h-80"
                    >
                        {{ painting.emoji }}
                    </span>
                </div>
            </div>

            <div class="min-w-0">
                <Link href="/cuadros" class="text-sm text-blue-600 hover:underline">
                    ← Volver a la galería
                </Link>

                <div class="mt-3 flex items-start justify-between gap-3">
                    <h1 class="break-words text-2xl font-bold text-gray-800 sm:text-3xl">
                        {{ painting.title }}
                    </h1>

                    <button
                        type="button"
                        class="shrink-0 rounded-full p-2"
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
                        <HeroIcon
                            name="heart"
                            :solid="painting.is_favorited"
                            class="h-7 w-7"
                        />
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
                        Estado: {{ statusLabel(painting.status) }}
                    </span>
                </div>

                <div class="mt-4 rounded-lg bg-amber-50 p-3">
                    <p class="flex items-center gap-2 text-sm font-medium text-amber-800">
                        <HeroIcon name="star" class="h-4 w-4" />
                        Valoración
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <p v-if="painting.ratings_avg" class="text-amber-700">
                            <span class="text-lg font-semibold">{{ painting.ratings_avg }}</span>
                            / 5
                        </p>
                        <p v-else class="text-sm text-amber-700">
                            Todavía no tiene valoraciones.
                        </p>

                        <div v-if="!painting.is_owner" class="flex items-center gap-1">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                class="rounded transition hover:scale-110"
                                :class="star <= hoverRating ? 'text-amber-400' : star <= painting.my_rating ? 'text-amber-500' : 'text-gray-300'"
                                :title="`Valorar con ${star} estrella${star > 1 ? 's' : ''}`"
                                :aria-label="`Valorar con ${star} estrella${star > 1 ? 's' : ''}`"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                @click="rate(star)"
                            >
                                <HeroIcon
                                    name="star"
                                    :solid="star <= (hoverRating || painting.my_rating)"
                                    class="h-7 w-7"
                                />
                            </button>
                        </div>
                        <p v-else class="text-xs text-amber-700">
                            No puedes valorar tu propia obra.
                        </p>
                    </div>

                    <p v-if="painting.my_rating && !painting.is_owner" class="mt-1 text-xs text-amber-700">
                        Tu valoración: {{ painting.my_rating }} / 5
                    </p>
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

                <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-500">
                    <span class="inline-flex items-center gap-1">
                        <HeroIcon name="heart" class="h-4 w-4 text-red-500" />
                        {{ painting.favorites_count }} favoritos
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <HeroIcon name="chat-bubble-left" class="h-4 w-4 text-gray-400" />
                        {{ painting.comments_count }} comentarios
                    </span>
                </div>

                <div v-if="painting.exhibitions.length" class="mt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Exposiciones
                    </h2>
                    <ul class="mt-2 space-y-2">
                        <li
                            v-for="exhibition in painting.exhibitions"
                            :key="exhibition.id"
                        >
                            <Link
                                :href="`/exposiciones/${exhibition.id}`"
                                class="block rounded-lg bg-white p-3 text-sm shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <p class="font-medium text-gray-800">
                                    {{ exhibition.title }}
                                </p>
                                <p class="text-gray-500">
                                    <span v-if="exhibition.starts_at">
                                        Desde {{ formatDate(exhibition.starts_at) }}
                                    </span>
                                    <span v-if="exhibition.ends_at">
                                        · hasta {{ formatDate(exhibition.ends_at) }}
                                    </span>
                                </p>
                            </Link>
                        </li>
                    </ul>
                </div>

                <div v-if="painting.can_edit" class="mt-6 flex flex-wrap gap-4 text-sm">
                    <Link
                        :href="`/cuadros/${painting.id}/edit`"
                        class="text-blue-600 hover:underline"
                    >
                        Editar obra
                    </Link>

                    <button
                        v-if="painting.can_delete"
                        type="button"
                        class="text-red-600 hover:underline"
                        @click="destroy"
                    >
                        Eliminar obra
                    </button>
                </div>
            </div>
        </div>

        <section class="mt-8 sm:mt-12">
            <h2 class="text-xl font-bold text-gray-800 sm:text-2xl">
                Comentarios ({{ painting.comments_count }})
            </h2>

            <form
                class="mt-4 rounded-xl bg-white p-4 shadow-sm sm:p-6"
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
                    v-for="comment in comments.data"
                    :key="comment.id"
                    class="rounded-xl bg-white p-4 shadow-sm"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                        <span class="font-medium text-gray-800">
                            {{ comment.user.name }}
                        </span>
                        <span class="text-gray-500">{{ comment.created_at }}</span>
                    </div>

                    <template v-if="editingCommentId === comment.id">
                        <form class="mt-2" @submit.prevent="submitEditComment(comment)">
                            <textarea
                                v-model="editForm.body"
                                rows="3"
                                class="w-full rounded-lg border px-3 py-2 text-sm"
                                required
                            />
                            <p v-if="editForm.errors.body" class="mt-1 text-xs text-red-600">
                                {{ editForm.errors.body }}
                            </p>

                            <div class="mt-2 flex gap-3 text-sm">
                                <button
                                    type="submit"
                                    class="rounded-lg bg-blue-600 px-3 py-1.5 text-white hover:bg-blue-700"
                                    :disabled="editForm.processing"
                                >
                                    Guardar
                                </button>
                                <button
                                    type="button"
                                    class="text-gray-600 hover:underline"
                                    @click="cancelEdit"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </template>

                    <template v-else>
                        <p class="mt-2 break-words text-gray-700">{{ comment.body }}</p>

                        <div class="mt-2 flex flex-wrap gap-4 text-sm">
                            <button
                                v-if="comment.can_edit"
                                type="button"
                                class="text-blue-600 hover:underline"
                                @click="startEdit(comment)"
                            >
                                Editar
                            </button>

                            <button
                                v-if="comment.can_delete"
                                type="button"
                                class="text-red-600 hover:underline"
                                @click="destroyComment(comment)"
                            >
                                Eliminar
                            </button>
                        </div>
                    </template>

                    <div class="mt-3 space-y-3 border-l-2 border-gray-100 pl-4">
                        <div v-for="reply in comment.replies" :key="reply.id">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                                <span class="font-medium text-gray-700">
                                    {{ reply.user.name }}
                                </span>
                                <span class="text-xs text-gray-400">{{ reply.created_at }}</span>
                            </div>

                            <template v-if="editingCommentId === reply.id">
                                <form class="mt-1" @submit.prevent="submitEditComment(reply)">
                                    <textarea
                                        v-model="editForm.body"
                                        rows="2"
                                        class="w-full rounded-lg border px-3 py-2 text-sm"
                                        required
                                    />
                                    <p v-if="editForm.errors.body" class="mt-1 text-xs text-red-600">
                                        {{ editForm.errors.body }}
                                    </p>

                                    <div class="mt-2 flex gap-3 text-sm">
                                        <button
                                            type="submit"
                                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-white hover:bg-blue-700"
                                            :disabled="editForm.processing"
                                        >
                                            Guardar
                                        </button>
                                        <button
                                            type="button"
                                            class="text-gray-600 hover:underline"
                                            @click="cancelEdit"
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </template>

                            <template v-else>
                                <p class="mt-1 break-words text-sm text-gray-600">
                                    {{ reply.body }}
                                </p>

                                <div class="mt-1 flex flex-wrap gap-4 text-xs">
                                    <button
                                        v-if="reply.can_edit"
                                        type="button"
                                        class="text-blue-600 hover:underline"
                                        @click="startEdit(reply)"
                                    >
                                        Editar
                                    </button>

                                    <button
                                        v-if="reply.can_delete"
                                        type="button"
                                        class="text-red-600 hover:underline"
                                        @click="destroyComment(reply)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </template>
                        </div>

                        <form
                            v-if="replyingTo === comment.id"
                            class="mt-1"
                            @submit.prevent="submitReply(comment)"
                        >
                            <textarea
                                v-model="replyForm.body"
                                rows="2"
                                :placeholder="`Responder a ${comment.user.name}...`"
                                class="w-full rounded-lg border px-3 py-2 text-sm"
                                required
                            />
                            <p v-if="replyForm.errors.body" class="mt-1 text-xs text-red-600">
                                {{ replyForm.errors.body }}
                            </p>

                            <div class="mt-2 flex gap-3 text-sm">
                                <button
                                    type="submit"
                                    class="rounded-lg bg-blue-600 px-3 py-1.5 text-white hover:bg-blue-700"
                                    :disabled="replyForm.processing"
                                >
                                    Responder
                                </button>
                                <button
                                    type="button"
                                    class="text-gray-600 hover:underline"
                                    @click="cancelReply"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </form>

                        <button
                            v-else
                            type="button"
                            class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:underline"
                            @click="startReply(comment)"
                        >
                            <HeroIcon name="arrow-uturn-left" class="h-3.5 w-3.5" />
                            Responder
                        </button>
                    </div>
                </div>

                <p v-if="!comments.data.length" class="text-gray-500">
                    Todavía no hay comentarios. Sé el primero.
                </p>
            </div>

            <Pagination
                v-if="comments.has_pages"
                :links="comments.links"
                class="mt-8"
            />
        </section>
    </Layout>
</template>

<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Pagination from "../../shared/Pagination.vue";
import HeroIcon from "../../shared/HeroIcon.vue";

const props = defineProps({
    painting: {
        type: Object,
        required: true,
    },
    comments: {
        type: Object,
        default: () => ({ data: [], links: null, has_pages: false }),
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

const replyForm = useForm({
    body: "",
    parent_id: null,
});

const editForm = useForm({
    body: "",
});

const form = useForm({});

const replyingTo = ref(null);
const editingCommentId = ref(null);
const hoverRating = ref(0);

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

const rate = (rating) => {
    router.post(
        `/cuadros/${props.painting.id}/rating`,
        { rating },
        {
            preserveScroll: true,
            only: ['painting', 'flash'],
        },
    );
};

const submitComment = () => {
    commentForm.post(`/cuadros/${props.painting.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
};

const startReply = (comment) => {
    replyingTo.value = comment.id;
    replyForm.body = "";
    replyForm.parent_id = comment.id;
};

const cancelReply = () => {
    replyingTo.value = null;
    replyForm.reset();
};

const submitReply = (comment) => {
    replyForm.post(`/cuadros/${props.painting.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => {
            replyingTo.value = null;
            replyForm.reset();
        },
    });
};

const startEdit = (comment) => {
    editingCommentId.value = comment.id;
    editForm.body = comment.body;
};

const cancelEdit = () => {
    editingCommentId.value = null;
    editForm.reset();
};

const submitEditComment = (comment) => {
    editForm.post(`/cuadros/${props.painting.id}/comments/${comment.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingCommentId.value = null;
            editForm.reset();
        },
    });
};

const destroyComment = (comment) => {
    if (confirm("¿Seguro que quieres eliminar este comentario?")) {
        router.delete(`/cuadros/${props.painting.id}/comments/${comment.id}`, {
            preserveScroll: true,
        });
    }
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

const statusLabel = (status) =>
    ({ sold: 'Vendida', reserved: 'Reservada', available: 'Disponible' })[status] || 'Disponible';

const formatDate = (date) =>
    new Date(date).toLocaleDateString("es-ES", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });

const formatPrice = (price) =>
    Number(price).toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
    });
</script>
