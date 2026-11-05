<template>
    <Link
        :href="href"
        :method="method"
        :as="as"
        class="nav-link relative block w-full touch-manipulation rounded-lg px-3 py-2 text-left text-sm font-medium text-gray-700 transition duration-150 ease-out hover:bg-indigo-50 hover:text-indigo-700 hover:shadow-sm active:scale-[0.98] active:bg-indigo-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:text-gray-200 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-200 dark:active:bg-indigo-500/30 dark:focus-visible:ring-offset-gray-900"
        :class="{
            'nav-link--active bg-indigo-100 font-bold text-indigo-800 shadow-sm ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 hover:text-indigo-800 dark:bg-indigo-500/30 dark:text-indigo-100 dark:ring-indigo-400/50 dark:hover:bg-indigo-500/30 dark:hover:text-indigo-100':
                active,
        }"
        :aria-current="active ? 'page' : undefined"
    >
        <slot />
    </Link>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: { Link },
    props: {
        href: String,
        active: Boolean,
        method: {
            type: String,
            default: undefined,
        },
        as: {
            type: String,
            default: undefined,
        },
    },
};
</script>

<style scoped>
.nav-link--active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    height: 60%;
    width: 3px;
    transform: translateY(-50%);
    border-radius: 9999px;
    background-color: currentColor;
    opacity: 0.75;
}

@media (prefers-reduced-motion: reduce) {
    .nav-link {
        transition: none;
    }
}
</style>
