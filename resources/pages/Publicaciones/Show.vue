<template>
    <Head :title="publication.title" />

    <Layout>
        <Link
            href="/publicaciones"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
            ← Volver a publicaciones
        </Link>

        <article class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm">
            <div
                v-if="publicationImage || publication.painting"
                class="flex h-40 items-center justify-center bg-gray-100 text-6xl sm:h-48 sm:text-7xl"
            >
                <Link
                    v-if="publication.painting"
                    :href="`/cuadros/${publication.painting.id}`"
                    class="flex h-full w-full items-center justify-center transition hover:opacity-90"
                    :title="`Ver ${publication.painting.title}`"
                >
                    <img
                        v-if="publicationImage"
                        :src="publicationImage"
                        :alt="publication.title"
                        class="h-full w-full object-cover"
                    />
                    <span v-else>{{ publication.painting.emoji }}</span>
                </Link>
                <img
                    v-else
                    :src="publicationImage"
                    :alt="publication.title"
                    class="h-full w-full object-cover"
                />
            </div>

            <div class="p-5 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h1 class="break-words text-2xl font-bold text-gray-800 sm:text-4xl">
                            {{ publication.title }}
                        </h1>

                        <p class="mt-2 flex flex-wrap items-center gap-x-2 text-sm text-gray-500">
                            <span>
                                Por
                                {{ publication.author?.name ?? "Anónimo" }}
                            </span>
                            <span v-if="publication.date">· {{ publication.date }}</span>
                            <span v-if="publication.category">· {{ publication.category.name }}</span>
                        </p>
                    </div>

                    <span
                        v-if="!publication.is_published"
                        class="w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700"
                    >
                        Borrador
                    </span>
                </div>

                <p
                    v-if="publication.summary"
                    class="mt-5 text-base text-gray-600 sm:text-lg"
                >
                    {{ publication.summary }}
                </p>

                <p class="mt-6 whitespace-pre-line break-words leading-relaxed text-gray-700">
                    {{ publication.content }}
                </p>

                <div
                    v-if="publication.painting"
                    class="mt-8 rounded-lg bg-gray-50 p-4"
                >
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Obra relacionada
                    </p>
                    <Link
                        :href="`/cuadros/${publication.painting.id}`"
                        class="mt-2 flex items-center gap-3 hover:underline"
                    >
                        <img
                            v-if="publication.painting.image_url"
                            :src="publication.painting.image_url"
                            :alt="publication.painting.title"
                            class="h-12 w-12 rounded-md object-cover"
                        />
                        <span v-else class="text-3xl">
                            {{ publication.painting.emoji }}
                        </span>
                        <span class="font-medium text-gray-800">
                            {{ publication.painting.title }}
                        </span>
                    </Link>
                </div>

                <div class="mt-8 flex gap-4 text-sm text-gray-500">
                    <span>💬 {{ publication.comments_count }} comentarios</span>
                </div>

                <div v-if="publication.can_edit" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Link
                        :href="`/publicaciones/${publication.id}/edit`"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700 sm:w-auto"
                    >
                        Editar
                    </Link>

                    <button
                        type="button"
                        class="w-full text-sm text-red-600 hover:underline sm:w-auto"
                        @click="destroy"
                    >
                        Eliminar publicación
                    </button>
                </div>
            </div>
        </article>

        <section class="mt-8 sm:mt-12">
            <h2 class="text-xl font-bold text-gray-800 sm:text-2xl">
                Comentarios ({{ publication.comments_count }})
            </h2>

            <form
                class="mt-4 rounded-xl bg-white p-4 shadow-sm sm:p-6"
                @submit.prevent="submitComment"
            >
                <textarea
                    v-model="commentForm.body"
                    rows="3"
                    placeholder="Escribe un comentario sobre esta publicación..."
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
                    class="mt-3 w-full rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50 sm:w-auto"
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
                        <span class="text-gray-500">
                            {{ comment.created_at }}
                        </span>
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
                            class="text-xs font-medium text-blue-600 hover:underline"
                            @click="startReply(comment)"
                        >
                            ↩︎ Responder
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
import { computed, ref } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import Pagination from "../../shared/Pagination.vue";

const props = defineProps({
    publication: {
        type: Object,
        required: true,
    },
    comments: {
        type: Object,
        default: () => ({ data: [], links: null, has_pages: false }),
    },
});

const publicationImage = computed(
    () => props.publication.image_url || props.publication.painting?.image_url || null,
);

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

const replyingTo = ref(null);
const editingCommentId = ref(null);

const submitComment = () => {
    commentForm.post(`/publicaciones/${props.publication.id}/comments`, {
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

const submitReply = () => {
    replyForm.post(`/publicaciones/${props.publication.id}/comments`, {
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
    editForm.post(`/publicaciones/${props.publication.id}/comments/${comment.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingCommentId.value = null;
            editForm.reset();
        },
    });
};

const destroyComment = (comment) => {
    if (confirm("¿Seguro que quieres eliminar este comentario?")) {
        router.delete(`/publicaciones/${props.publication.id}/comments/${comment.id}`, {
            preserveScroll: true,
        });
    }
};

const destroy = () => {
    if (confirm("¿Seguro que quieres eliminar esta publicación?")) {
        router.delete(`/publicaciones/${props.publication.id}`);
    }
};
</script>
