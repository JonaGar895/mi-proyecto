<template>
    <Link
        :href="href"
        :method="method"
        :as="as"
        class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-black transition hover:bg-gray-100 hover:no-underline dark:text-gray-100 dark:hover:bg-gray-800"
        :class="{ 'bg-gray-100 font-bold underline dark:bg-gray-800': active }"
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
