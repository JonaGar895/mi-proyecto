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
            <Link
                v-if="publication.painting"
                :href="`/cuadros/${publication.painting.id}`"
                class="flex h-48 items-center justify-center bg-gray-100 text-7xl transition hover:opacity-90"
                :title="`Ver ${publication.painting.title}`"
            >
                <img
                    v-if="publication.painting.image_url"
                    :src="publication.painting.image_url"
                    :alt="publication.painting.title"
                    class="h-full w-full object-cover"
                />
                <span v-else>{{ publication.painting.emoji }}</span>
            </Link>

            <div class="p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800">
                            {{ publication.title }}
                        </h1>

                        <p class="mt-2 text-sm text-gray-500">
                            Por
                            {{ publication.author?.name ?? "Anónimo" }}
                            <span v-if="publication.date">
                                · {{ publication.date }}
                            </span>
                            <span v-if="publication.category">
                                · {{ publication.category.name }}
                            </span>
                        </p>
                    </div>

                    <span
                        v-if="!publication.is_published"
                        class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700"
                    >
                        Borrador
                    </span>
                </div>

                <p
                    v-if="publication.summary"
                    class="mt-5 text-lg text-gray-600"
                >
                    {{ publication.summary }}
                </p>

                <p class="mt-6 whitespace-pre-line leading-relaxed text-gray-700">
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
                        <span class="text-3xl">
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

                <div v-if="publication.can_edit" class="mt-6 flex items-center gap-4">
                    <Link
                        :href="`/publicaciones/${publication.id}/edit`"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        Editar
                    </Link>

                    <button
                        type="button"
                        class="text-sm text-red-600 hover:underline"
                        @click="destroy"
                    >
                        Eliminar publicación
                    </button>
                </div>
            </div>
        </article>

        <section class="mt-12">
            <h2 class="text-2xl font-bold text-gray-800">
                Comentarios ({{ publication.comments_count }})
            </h2>

            <form
                class="mt-4 rounded-xl bg-white p-4 shadow-sm"
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
                        <span class="text-gray-500">
                            {{ comment.created_at }}
                        </span>
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

const props = defineProps({
    publication: {
        type: Object,
        required: true,
    },
    comments: {
        type: Array,
        default: () => [],
    },
});

const commentForm = useForm({
    body: "",
});

const submitComment = () => {
    commentForm.post(`/publicaciones/${props.publication.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
};

const destroy = () => {
    if (confirm("¿Seguro que quieres eliminar esta publicación?")) {
        router.delete(`/publicaciones/${props.publication.id}`);
    }
};
</script>
