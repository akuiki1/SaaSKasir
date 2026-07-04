<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, type HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();

const toko = computed(() => usePage().props.toko);
// Fallback ke logo default SiKasir bila toko belum unggah logo sendiri —
// JANGAN pernah fallback ke logo toko lain.
const logoSrc = computed(() => toko.value?.logo_url ?? '/images/logo.png');
const logoAlt = computed(() => toko.value?.nama ?? 'SiKasir');
</script>

<template>
    <img :src="logoSrc" :alt="logoAlt" class="object-contain" v-bind="$attrs" />
</template>
