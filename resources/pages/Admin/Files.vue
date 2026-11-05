<template>
    <Head title="Gestión de archivos" />

    <Layout>
        <div
            class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between"
        >
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 sm:text-4xl">
                    🗂️ Gestión de archivos
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Revisa y limpia las imágenes y los registros duplicados de la galería.
                </p>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <button
                    type="button"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-center font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="form.processing"
                    @click="clean(false)"
                >
                    {{ form.processing && ! form.strict ? "Limpiando..." : "Limpieza segura" }}
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-red-600 px-4 py-2 text-center font-medium text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="form.processing"
                    @click="clean(true)"
                >
                    {{ form.processing && form.strict ? "Limpiando..." : "Eliminar títulos repetidos" }}
                </button>
            </div>
        </div>

        <div
            class="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-100"
        >
            <p class="font-medium">Cómo funciona la limpieza</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li><strong>Limpieza segura:</strong> fusiona solo registros duplicados exactos (mismo título, resumen/descripción y contenido).</li>
                <li><strong>Eliminar títulos repetidos:</strong> además fusiona los registros que comparten título aunque el contenido sea distinto, conservando el más antiguo.</li>
                <li>Los comentarios de los registros eliminados se trasladan al registro conservado.</li>
                <li>Se eliminan los archivos duplicados y los que no están asociados a ningún registro.</li>
                <li>Los títulos en conflicto se muestran abajo para revisarlos antes de usar la limpieza estricta.</li>
            </ul>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
                <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Archivos
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">
                    {{ report.files.total }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ report.files.size_human }} · {{ report.files.referenced }} referenciados
                </p>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
                <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Huérfanos
                </p>
                <p class="mt-2 text-3xl font-bold text-amber-600 dark:text-amber-400">
                    {{ report.files.orphans }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Archivos sin registro asociado
                </p>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
                <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Archivos duplicados
                </p>
                <p class="mt-2 text-3xl font-bold text-orange-600 dark:text-orange-400">
                    {{ report.files.duplicates }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Copias con el mismo contenido
                </p>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
                <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Registros duplicados
                </p>
                <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">
                    {{ report.records.duplicate_entries }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ report.records.duplicate_groups }} grupos exactos
                </p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
                <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Referencias sin archivo
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">
                    {{ report.files.missing }}
                </p>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
                <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Títulos en conflicto
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">
                    {{ report.records.conflicts }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Mismo título con contenido distinto
                </p>
            </div>
        </div>

        <section class="mt-8">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                Registros duplicados exactos
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Se conservará el registro indicado y se eliminarán los demás.
            </p>

            <div
                v-if="report.duplicate_records.length"
                class="mt-3 overflow-x-auto rounded-xl bg-white shadow-sm dark:bg-gray-900"
            >
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Título</th>
                            <th class="px-4 py-3">Cantidad</th>
                            <th class="px-4 py-3">IDs</th>
                            <th class="px-4 py-3">Se conserva</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr
                            v-for="group in report.duplicate_records"
                            :key="`${group.type}-${group.keep}`"
                        >
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ group.type_label }}
                            </td>
                            <td class="max-w-xs break-words px-4 py-3 font-medium text-gray-800 dark:text-gray-100">
                                {{ group.label }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ group.count }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                {{ group.ids.join(", ") }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                    ID {{ group.keep }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p
                v-else
                class="mt-3 rounded-xl bg-white p-4 text-sm text-gray-500 shadow-sm dark:bg-gray-900 dark:text-gray-400"
            >
                No hay registros duplicados exactos.
            </p>
        </section>

        <section class="mt-8">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                Archivos duplicados
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Archivos con el mismo contenido. Se conservará el que esté referenciado.
            </p>

            <div
                v-if="report.duplicate_files.length"
                class="mt-3 space-y-3"
            >
                <div
                    v-for="group in report.duplicate_files"
                    :key="group.hash"
                    class="rounded-xl bg-white p-4 shadow-sm dark:bg-gray-900"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-mono text-xs text-gray-500 dark:text-gray-400">
                            SHA-256: {{ group.hash.slice(0, 16) }}…
                        </p>
                        <span class="rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-800 dark:bg-orange-900/40 dark:text-orange-200">
                            {{ group.count }} copias
                        </span>
                    </div>

                    <ul class="mt-3 space-y-1 text-sm">
                        <li
                            v-for="path in group.paths"
                            :key="path"
                            class="flex flex-wrap items-center gap-2 break-all text-gray-700 dark:text-gray-300"
                        >
                            <span class="font-mono text-xs">{{ path }}</span>
                            <span
                                v-if="path === group.keep"
                                class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/40 dark:text-green-200"
                            >
                                conservar
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <p
                v-else
                class="mt-3 rounded-xl bg-white p-4 text-sm text-gray-500 shadow-sm dark:bg-gray-900 dark:text-gray-400"
            >
                No hay archivos duplicados.
            </p>
        </section>

        <section class="mt-8">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                Archivos huérfanos
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Archivos que no pertenecen a ninguna obra ni publicación. Se eliminarán en la limpieza.
            </p>

            <ul
                v-if="report.orphans.length"
                class="mt-3 max-h-80 space-y-2 overflow-y-auto rounded-xl bg-white p-4 text-sm shadow-sm dark:bg-gray-900"
            >
                <li
                    v-for="path in report.orphans"
                    :key="path"
                    class="break-all font-mono text-xs text-gray-700 dark:text-gray-300"
                >
                    {{ path }}
                </li>
            </ul>

            <p
                v-else
                class="mt-3 rounded-xl bg-white p-4 text-sm text-gray-500 shadow-sm dark:bg-gray-900 dark:text-gray-400"
            >
                No hay archivos huérfanos.
            </p>
        </section>

        <section
            v-if="report.missing.length"
            class="mt-8"
        >
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                Referencias sin archivo
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Estos registros apuntan a una imagen que ya no existe en el disco.
            </p>

            <ul class="mt-3 space-y-2 rounded-xl bg-white p-4 text-sm shadow-sm dark:bg-gray-900">
                <li
                    v-for="path in report.missing"
                    :key="path"
                    class="break-all font-mono text-xs text-red-600 dark:text-red-400"
                >
                    {{ path }}
                </li>
            </ul>
        </section>

        <section
            v-if="report.conflicts.length"
            class="mt-8"
        >
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                Títulos en conflicto
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Tienen el mismo título pero contenido distinto. Deben revisarse manualmente.
            </p>

            <div class="mt-3 overflow-x-auto rounded-xl bg-white shadow-sm dark:bg-gray-900">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Título</th>
                            <th class="px-4 py-3">Registros</th>
                            <th class="px-4 py-3">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr
                            v-for="conflict in report.conflicts"
                            :key="`${conflict.type}-${conflict.label}`"
                        >
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ conflict.type_label }}
                            </td>
                            <td class="max-w-xs break-words px-4 py-3 font-medium text-gray-800 dark:text-gray-100">
                                {{ conflict.label }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ conflict.count }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                {{ conflict.detail }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </Layout>
</template>

<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "../../shared/Layout.vue";

defineProps({
    report: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    strict: false,
});

function clean(strict) {
    const message = strict
        ? "Se eliminarán TODOS los registros que compartan título, conservando el más antiguo, además de los archivos duplicados y huérfanos. Esta acción no se puede deshacer. ¿Continuar?"
        : "Se eliminarán los archivos duplicados y huérfanos, y se fusionarán los registros duplicados exactos. ¿Continuar?";

    if (! window.confirm(message)) {
        return;
    }

    form.strict = strict;
    form.post("/admin/archivos/limpiar", {
        preserveScroll: true,
    });
}
</script>
