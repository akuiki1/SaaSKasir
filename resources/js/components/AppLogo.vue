<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps<{
    /** Baris kecil di bawah nama brand (mis. peran pengguna). Opsional. */
    subtitle?: string;
}>();

const namaToko = computed(() => usePage().props.toko?.nama ?? 'SiKasir');
</script>

<template>
    <div class="flex aspect-square size-10 items-center justify-center">
        <AppLogoIcon class="size-10" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="truncate leading-tight font-semibold">{{
            namaToko
        }}</span>
        <span
            v-if="subtitle"
            class="truncate text-xs leading-tight font-normal text-muted-foreground"
            >{{ subtitle }}</span
        >
    </div>
</template>
