<template>
    <nav class="relative" aria-label="Navegación principal">
        <!-- Navegación de escritorio -->
        <ul class="hidden list-none items-center gap-1 xl:flex xl:gap-2">
            <li v-for="item in items" :key="item.href">
                <NavLink
                    :href="item.href"
                    :active="item.active"
                    :method="item.method"
                    :as="item.as"
                >
                    {{ item.label }}
                </NavLink>
            </li>
        </ul>

        <!-- Botón de menú para móvil y tablet -->
        <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900 xl:hidden"
            :aria-expanded="open"
            aria-controls="mobile-menu"
            :aria-label="open ? 'Cerrar menú' : 'Abrir menú'"
            @click="open = !open"
        >
            <svg
                v-if="!open"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg
                v-else
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Menú desplegable en móvil y tablet -->
        <div
            v-if="open"
            id="mobile-menu"
            class="absolute right-0 top-full z-50 mt-3 w-64 rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-700 dark:bg-gray-900 xl:hidden"
        >
            <ul class="flex list-none flex-col gap-1" @click="open = false">
                <li v-for="item in items" :key="item.href">
                    <NavLink
                        :href="item.href"
                        :active="item.active"
                        :method="item.method"
                        :as="item.as"
                    >
                        {{ item.label }}
                    </NavLink>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import NavLink from "./NavLink.vue";

const page = usePage();
const open = ref(false);

const items = computed(() => {
    const links = [
        {
            href: "/",
            label: "Inicio",
            active: page.component === "Home",
        },
        {
            href: "/cuadros",
            label: "Cuadros",
            active: page.component.startsWith("Paintings"),
        },
        {
            href: "/publicaciones",
            label: "Publicaciones",
            active: page.component.startsWith("Publicaciones"),
        },
        {
            href: "/users/list",
            label: "Usuarios",
            active: page.component.startsWith("Users"),
        },
        {
            href: "/inquiries",
            label: "Solicitudes",
            active:
                page.component === "Inquiries/Index" ||
                page.component === "Inquiries/History",
        },
        {
            href: "/mi-cuenta",
            label: "Mi cuenta",
            active:
                page.component === "Settings" ||
                page.component === "Account/Index",
        },
    ];

    if (page.props.auth?.user?.is_super_admin) {
        links.push({
            href: "/cuenta-super-admin",
            label: "Cuenta de ventas",
            active: page.component === "Account/SuperAdmin",
        });
    }

    links.push({
        href: "/logout",
        label: "Cerrar sesión",
        method: "post",
        as: "button",
    });

    return links;
});

watch(
    () => page.url,
    () => {
        open.value = false;
    },
);
</script>
