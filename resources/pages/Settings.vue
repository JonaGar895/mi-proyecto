<template>
    <Head title="Mi cuenta" />

    <Layout>
        <h1 class="text-4xl font-bold text-gray-800">⚙️ Mi cuenta</h1>
        <p class="mt-2 text-gray-600">
            Hola, {{ $page.props.auth.user.name || $page.props.auth.user.username || $page.props.auth.user.email }}
            <span class="text-sm text-gray-400">
                ({{ $page.props.auth.user.email }})
            </span>
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">
                    Obras publicadas
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800">
                    {{ stats.paintings }}
                </p>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">
                    Favoritos
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800">
                    {{ stats.favorites }}
                </p>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">
                    Comentarios
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800">
                    {{ stats.comments }}
                </p>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-wide text-gray-500">
                    Publicaciones
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800">
                    {{ stats.publications }}
                </p>
            </div>
        </div>

        <section class="mt-10 rounded-xl bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-800">Mi saldo</h2>
            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ formatPrice(balance) }}</p>
            <p class="mt-1 text-sm text-gray-500">Este saldo se usa para comprar obras. Las compras requieren aprobación de un administrador.</p>
        </section>

        <section class="mt-10">
            <h2 class="text-2xl font-bold text-gray-800">Mis compras</h2>
            <p class="mt-1 text-gray-600">Obras que has comprado (aprobadas por el administrador).</p>
            <div v-if="purchasedPaintings?.length" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link v-for="painting in purchasedPaintings" :key="painting.id" :href="`/cuadros/${painting.id}`" class="rounded-xl bg-white p-4 shadow-sm hover:shadow-md">
                    <div class="text-4xl">{{ painting.emoji || '🎨' }}</div>
                    <p class="mt-2 font-semibold text-gray-800">{{ painting.title }}</p>
                    <p class="text-sm text-gray-500">{{ painting.artist }}</p>
                    <p v-if="painting.price" class="mt-1 text-sm text-gray-600">{{ formatPrice(painting.price) }}</p>
                    <p v-if="painting.sold_at" class="mt-1 text-xs text-gray-400">Comprada el {{ painting.sold_at }}</p>
                </Link>
            </div>
            <p v-else class="mt-3 text-sm text-gray-500">Aún no has comprado ninguna obra.</p>
        </section>

        <section class="mt-10">
            <h2 class="text-2xl font-bold text-gray-800">Mis solicitudes de compra</h2>
            <p class="mt-1 text-gray-600">Estado de las solicitudes enviadas.</p>
            <div v-if="myInquiries?.length" class="mt-4 space-y-3">
                <div v-for="inq in myInquiries" :key="inq.id" class="rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <Link :href="`/cuadros/${inq.painting?.id}`" class="font-medium text-gray-800 hover:underline">{{ inq.painting?.title }}</Link>
                        <span class="rounded-full px-2 py-1 text-xs" :class="inqStatusClass(inq.status)">{{ inqStatusLabel(inq.status) }}</span>
                    </div>
                    <p class="text-sm text-gray-500">Vendedor: {{ inq.seller?.name }} · {{ inq.created_at }}</p>
                    <p v-if="inq.painting?.price" class="text-sm text-gray-600">{{ formatPrice(inq.painting.price) }}</p>
                </div>
            </div>
            <p v-else class="mt-3 text-sm text-gray-500">No has enviado ninguna solicitud.</p>
        </section>

        <section class="mt-10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-2xl font-bold text-gray-800">
                    Mis publicaciones
                </h2>

                <Link
                    href="/publicaciones/create"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700"
                >
                    + Nueva publicación
                </Link>
            </div>

            <div v-if="myPublications.length" class="mt-4 space-y-3">
                <article
                    v-for="publication in myPublications"
                    :key="publication.id"
                    class="flex flex-wrap items-start justify-between gap-4 rounded-xl bg-white p-4 shadow-sm"
                >
                    <div class="min-w-0">
                        <Link
                            :href="`/publicaciones/${publication.id}`"
                            class="block font-medium text-gray-800 hover:underline"
                        >
                            {{ publication.title }}
                        </Link>

                        <div
                            class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500"
                        >
                            <span
                                v-if="publication.is_published"
                                class="rounded-full bg-green-100 px-2 py-0.5 text-green-700"
                            >
                                Publicada
                            </span>
                            <span
                                v-else
                                class="rounded-full bg-amber-100 px-2 py-0.5 text-amber-700"
                            >
                                Borrador
                            </span>

                            <span v-if="publication.category">
                                {{ publication.category }}
                            </span>
                            <span>{{ publication.date }}</span>
                            <span>💬 {{ publication.comments_count }}</span>
                        </div>

                        <p
                            v-if="publication.summary"
                            class="mt-2 line-clamp-2 text-sm text-gray-600"
                        >
                            {{ publication.summary }}
                        </p>
                    </div>

                    <div class="flex gap-3 text-sm">
                        <Link
                            :href="`/publicaciones/${publication.id}`"
                            class="text-blue-600 hover:underline"
                        >
                            Ver
                        </Link>
                        <Link
                            :href="`/publicaciones/${publication.id}/edit`"
                            class="text-gray-600 hover:underline"
                        >
                            Editar
                        </Link>
                    </div>
                </article>
            </div>

            <p v-else class="mt-4 text-gray-500">
                Todavía no has escrito ninguna publicación.
                <Link
                    href="/publicaciones/create"
                    class="text-blue-600 hover:underline"
                >
                    Crea la primera
                </Link>.
            </p>
        </section>

        <section class="mt-10">
            <h2 class="text-2xl font-bold text-gray-800">Mis favoritos</h2>

            <div
                v-if="favoritePaintings.length"
                class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="painting in favoritePaintings"
                    :key="painting.id"
                    :href="`/cuadros/${painting.id}`"
                    class="flex items-center gap-4 rounded-xl bg-white p-4 shadow-sm hover:shadow-md"
                >
                    <span class="text-4xl">{{ painting.emoji }}</span>
                    <span>
                        <span class="block font-medium text-gray-800">
                            {{ painting.title }}
                        </span>
                        <span class="block text-sm text-gray-500">
                            {{ painting.artist || "Anónimo" }}
                        </span>
                    </span>
                </Link>
            </div>

            <p v-else class="mt-4 text-gray-500">
                Todavía no has guardado ninguna obra. Explora la
                <Link href="/cuadros" class="text-blue-600 hover:underline">
                    galería
                </Link>.
            </p>
        </section>
    </Layout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    stats: {
        type: Object,
        default: () => ({
            paintings: 0,
            favorites: 0,
            comments: 0,
            publications: 0,
        }),
    },
    favoritePaintings: {
        type: Array,
        default: () => [],
    },
    myPublications: {
        type: Array,
        default: () => [],
    },
    purchasedPaintings: {
        type: Array,
        default: () => [],
    },
    myInquiries: {
        type: Array,
        default: () => [],
    },
    balance: {
        type: [Number, String],
        default: 0,
    },
});

const formatPrice = (price) => {
    if (price === null || price === undefined || price === "") {
        return "-";
    }

    const value = Number(price);

    if (Number.isNaN(value)) {
        return price;
    }

    return value.toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
    });
};

const inqStatusLabel = (status) => {
    const map = {
        pending: "Pendiente de aprobación",
        contacted: "Contactado",
        declined: "Rechazado",
        rejected: "Rechazado",
        closed: "Cerrado",
        approved: "Aprobado",
    };

    return map[status] || status;
};

const inqStatusClass = (status) => {
    const map = {
        pending: "bg-amber-100 text-amber-700",
        contacted: "bg-blue-100 text-blue-700",
        declined: "bg-red-100 text-red-700",
        rejected: "bg-red-100 text-red-700",
        closed: "bg-gray-100 text-gray-600",
        approved: "bg-emerald-100 text-emerald-700",
    };

    return map[status] || "bg-gray-100 text-gray-600";
};
</script>
