<template>
    <nav class="relative" aria-label="Navegación principal">
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

        <Link
            href="/notificaciones"
            class="relative mr-1 inline-flex h-10 w-10 touch-manipulation items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm transition duration-200 ease-out hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 active:scale-90 active:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:border-indigo-400/50 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-200 dark:active:bg-indigo-500/30 dark:focus:ring-offset-gray-900"
            :class="{ 'text-indigo-700 dark:text-indigo-300': unread > 0 }"
            aria-label="Notificaciones"
        >
            <svg
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>

            <span
                v-if="unread > 0"
                class="absolute -right-1 -top-1 inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold leading-5 text-white"
            >
                {{ unread > 99 ? "99+" : unread }}
            </span>
        </Link>

        <button
            type="button"
            class="inline-flex h-10 w-10 touch-manipulation items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm transition duration-200 ease-out hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 active:scale-90 active:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:border-indigo-400/50 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-200 dark:active:bg-indigo-500/30 dark:focus:ring-offset-gray-900 xl:hidden"
            :aria-expanded="open"
            aria-controls="mobile-menu"
            :aria-label="open ? 'Cerrar menú' : 'Abrir menú'"
            @click="toggleMenu"
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

        <Teleport to="body">
            <Transition name="drawer-backdrop">
                <div
                    v-if="open"
                    class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm xl:hidden"
                    aria-hidden="true"
                    @click="closeMenuImmediate"
                ></div>
            </Transition>

            <Transition name="drawer">
                <aside
                    v-if="open"
                    id="mobile-menu"
                    ref="drawerPanel"
                    class="fixed inset-y-0 right-0 z-50 flex w-72 max-w-[85vw] flex-col border-l border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900 xl:hidden"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="mobile-menu-title"
                >
                    <div
                        class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700"
                    >
                        <p
                            id="mobile-menu-title"
                            class="text-base font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Menú
                        </p>

                        <button
                            ref="closeButton"
                            type="button"
                            class="inline-flex h-9 w-9 touch-manipulation items-center justify-center rounded-full text-gray-500 transition duration-150 ease-out hover:bg-gray-100 hover:text-gray-700 active:scale-90 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200 dark:focus:ring-offset-gray-900"
                            aria-label="Cerrar menú"
                            @click="closeMenuImmediate"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <ul class="flex flex-1 list-none flex-col gap-1 overflow-y-auto p-3" @click="closeMenu">
                        <li
                            v-for="(item, index) in items"
                            :key="item.href"
                            class="drawer-item touch-manipulation"
                            :style="{ '--menu-index': index }"
                        >
                            <NavLink
                                :href="item.href"
                                :active="item.active"
                                :method="item.method"
                                :as="item.as"
                            >
                                {{ item.label }}
                                <span
                                    v-if="item.badge"
                                    class="ml-2 inline-block rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white"
                                >
                                    {{ item.badge }}
                                </span>
                            </NavLink>
                        </li>
                    </ul>

                    <div class="border-t border-gray-200 p-3 dark:border-gray-700">
                        <ThemeToggle variant="menu" />
                    </div>
                </aside>
            </Transition>
        </Teleport>
    </nav>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import NavLink from "./NavLink.vue";
import ThemeToggle from "./ThemeToggle.vue";

const page = usePage();
const open = ref(false);
const drawerPanel = ref(null);
const closeButton = ref(null);
let closeTimer = null;
let previousOverflow = "";

const unread = computed(() => Number(page.props.unreadNotifications ?? 0));

function clearCloseTimer() {
    if (closeTimer !== null) {
        clearTimeout(closeTimer);
        closeTimer = null;
    }
}

function toggleMenu() {
    open.value = !open.value;
}

function closeMenuImmediate() {
    clearCloseTimer();
    open.value = false;
}

function closeMenu() {
    clearCloseTimer();
    closeTimer = setTimeout(() => {
        open.value = false;
        closeTimer = null;
    }, 140);
}

function handleKeydown(event) {
    if (!open.value) {
        return;
    }

    if (event.key === "Escape") {
        event.preventDefault();
        closeMenuImmediate();
        return;
    }

    if (event.key !== "Tab" || !drawerPanel.value) {
        return;
    }

    const focusable = drawerPanel.value.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    );

    if (focusable.length === 0) {
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

function handleResize() {
    if (window.innerWidth >= 1280) {
        closeMenuImmediate();
    }
}

onMounted(() => {
    window.addEventListener("resize", handleResize);
    document.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", handleResize);
    document.removeEventListener("keydown", handleKeydown);
    clearCloseTimer();
    document.body.style.overflow = previousOverflow;
});

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
            active: page.component === "Paintings",
        },
        {
            href: "/mis-obras",
            label: "Mis obras",
            active: page.component === "Paintings/Mine",
        },
        {
            href: "/exposiciones",
            label: "Exposiciones",
            active: page.component.startsWith("Exposiciones"),
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
            href: "/notificaciones",
            label: "Notificaciones",
            badge: unread.value > 0 ? (unread.value > 99 ? "99+" : String(unread.value)) : null,
            active: page.component === "Notificaciones/Index",
        },
        {
            href: "/mi-cuenta",
            label: "Mi cuenta",
            active:
                page.component === "Settings" ||
                page.component === "Account/Index",
        },
    ];

    if (page.props.auth?.user?.is_admin) {
        links.push({
            href: "/admin",
            label: "Panel",
            active: page.component === "Admin/Dashboard",
        });
    }

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

watch(open, async (isOpen) => {
    if (typeof document === "undefined") {
        return;
    }

    if (isOpen) {
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = "hidden";
        await nextTick();
        closeButton.value?.focus();
    } else {
        document.body.style.overflow = previousOverflow;
    }
});

watch(
    () => page.url,
    () => {
        closeMenuImmediate();
    },
);
</script>

<style scoped>
.drawer-backdrop-enter-active {
    transition: opacity 250ms ease-out;
}

.drawer-backdrop-leave-active {
    transition: opacity 200ms ease-in;
}

.drawer-backdrop-enter-from,
.drawer-backdrop-leave-to {
    opacity: 0;
}

.drawer-enter-active {
    transition: transform 320ms cubic-bezier(0.16, 1, 0.3, 1);
}

.drawer-leave-active {
    transition: transform 220ms ease-in;
}

.drawer-enter-from,
.drawer-leave-to {
    transform: translateX(100%);
}

.drawer-enter-active .drawer-item {
    transition:
        opacity 220ms ease-out,
        transform 260ms cubic-bezier(0.16, 1, 0.3, 1);
    transition-delay: calc(var(--menu-index) * 35ms);
}

.drawer-enter-from .drawer-item {
    opacity: 0;
    transform: translateX(16px);
}

@media (prefers-reduced-motion: reduce) {
    .drawer-backdrop-enter-active,
    .drawer-backdrop-leave-active,
    .drawer-enter-active,
    .drawer-leave-active,
    .drawer-enter-active .drawer-item {
        transition: none;
    }

    .drawer-enter-from,
    .drawer-leave-to,
    .drawer-enter-from .drawer-item {
        transform: none;
    }
}
</style>
