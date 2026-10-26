<template>
    <Link
        :href="href"
        :method="method"
        :as="as"
        class="text-black hover:underline"
        :class="{ 'font-bold underline': active }"
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