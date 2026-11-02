<template>
    <div class="flex flex-wrap items-center gap-1 sm:gap-2">
        <Component
            :is="link.url ? 'Link' : 'span'"
            v-for="(link, index) in links"
            :key="index"
            :href="link.url"
            :aria-current="link.active ? 'page' : undefined"
            v-html="link.label"
            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg px-2 py-1 text-sm transition sm:px-3"
            :class="{
                'cursor-not-allowed text-gray-400 dark:text-gray-600': !link.url,
                'bg-indigo-600 font-bold text-white hover:bg-indigo-700': link.active,
                'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800': link.url && !link.active,
            }"
        />
    </div>
</template>

<script>
export default {
    props: {
        links: Array,
    },
};
</script>
