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
                    v-for="comment in comments"
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
                    <p class="mt-2 break-words text-gray-700">{{ comment.body }}</p>
                </div>

                <p v-if="!comments.length" class="text-gray-500">
                    Todavía no hay comentarios. Sé el primero.
                </p>
            </div>
        </section>
    </Layout>
</template>

<script setup>
import { computed } from "vue";
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

const publicationImage = computed(
    () => props.publication.image_url || props.publication.painting?.image_url || null,
);

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
