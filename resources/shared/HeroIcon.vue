<template>
    <component :is="icon" v-if="icon" />
    <span v-else aria-hidden="true">{{ name }}</span>
</template>

<script setup>
import { computed } from "vue";
import {
    ArrowUturnLeftIcon,
    BellIcon,
    BuildingLibraryIcon,
    ChartBarIcon,
    ChatBubbleLeftIcon,
    ClipboardDocumentCheckIcon,
    Cog6ToothIcon,
    HeartIcon as HeartOutlineIcon,
    MoonIcon,
    NewspaperIcon,
    PaintBrushIcon,
    PhotoIcon,
    ShoppingCartIcon,
    StarIcon as StarOutlineIcon,
    SunIcon,
} from "@heroicons/vue/24/outline";
import {
    HeartIcon as HeartSolidIcon,
    StarIcon as StarSolidIcon,
} from "@heroicons/vue/24/solid";

/**
 * Registro de iconos de heroicons (https://heroicons.com) disponibles por
 * nombre. Las notificaciones guardan el nombre del icono en la base de
 * datos; si el valor no coincide con ningun icono conocido (p. ej. los
 * emojis de notificaciones antiguas), se muestra el valor tal cual.
 */
const registry = {
    "arrow-uturn-left": { outline: ArrowUturnLeftIcon },
    bell: { outline: BellIcon },
    "building-library": { outline: BuildingLibraryIcon },
    "chart-bar": { outline: ChartBarIcon },
    "chat-bubble-left": { outline: ChatBubbleLeftIcon },
    "clipboard-document-check": { outline: ClipboardDocumentCheckIcon },
    "cog-6-tooth": { outline: Cog6ToothIcon },
    heart: { outline: HeartOutlineIcon, solid: HeartSolidIcon },
    moon: { outline: MoonIcon },
    newspaper: { outline: NewspaperIcon },
    "paint-brush": { outline: PaintBrushIcon },
    photo: { outline: PhotoIcon },
    "shopping-cart": { outline: ShoppingCartIcon },
    star: { outline: StarOutlineIcon, solid: StarSolidIcon },
    sun: { outline: SunIcon },
};

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    solid: {
        type: Boolean,
        default: false,
    },
});

const icon = computed(() => {
    const entry = registry[props.name];

    if (!entry) {
        return null;
    }

    return props.solid ? (entry.solid ?? entry.outline) : entry.outline;
});
</script>
