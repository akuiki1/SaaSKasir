<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Check, CreditCard, MessageCircle, Sparkles, X } from 'lucide-vue-next';
import { computed } from 'vue';
import { formatRupiah } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Langganan', href: '/admin/langganan' }],
    },
});

interface Tier {
    key: 'gratis' | 'warung' | 'bisnis';
    label: string;
    harga: number;
    urutan: number;
}

interface Riwayat {
    tier: string;
    nominal: number;
    metode: string;
    periode_mulai: string;
    periode_sampai: string;
    tanggal: string;
    catatan: string | null;
}

const props = defineProps<{
    tier_efektif: 'gratis' | 'warung' | 'bisnis';
    tier_langganan: 'gratis' | 'warung' | 'bisnis';
    langganan_sampai: string | null;
    tiers: Tier[];
    fitur: Record<string, string>; // nama fitur => tier minimum
    kontak_wa: string;
    riwayat: Riwayat[];
}>();

const urutanTier = (key: string): number =>
    props.tiers.find((t) => t.key === key)?.urutan ?? 0;

const tierAktif = computed(
    () => props.tiers.find((t) => t.key === props.tier_efektif) ?? null,
);

// Label fitur ber-gate yang mudah dibaca (untuk baris tabel perbandingan).
const fiturLabel: Record<string, string> = {
    laporan_keuangan: 'Laporan Keuangan (Laba Rugi)',
};

const fiturRows = computed(() =>
    Object.entries(props.fitur).map(([key, tierMin]) => ({
        label: fiturLabel[key] ?? key,
        tierMin,
    })),
);

const sampaiFormatted = computed(() => {
    if (!props.langganan_sampai) {
        return props.tier_langganan === 'gratis' ? null : 'Selamanya';
    }

    return new Date(props.langganan_sampai).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
});

const waUpgradeUrl = computed(() => {
    if (!props.kontak_wa) {
        return null;
    }

    const text = encodeURIComponent(
        'Halo SiKasir, saya ingin upgrade langganan toko saya.',
    );

    return `https://wa.me/${props.kontak_wa}?text=${text}`;
});

const metodeLabel: Record<string, string> = {
    manual: 'Manual',
    midtrans: 'Midtrans',
};
</script>

<template>
    <Head title="Langganan" />

    <div class="mx-auto max-w-4xl space-y-6 p-4 md:p-6">
        <!-- Status saat ini -->
        <div
            class="flex flex-col gap-4 rounded-2xl border border-sidebar-border/70 bg-card p-6 sm:flex-row sm:items-center sm:justify-between dark:border-sidebar-border"
        >
            <div class="flex items-start gap-3">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary"
                >
                    <CreditCard class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Paket saat ini</p>
                    <h1 class="text-xl font-bold">
                        {{ tierAktif?.label ?? 'Gratis' }}
                    </h1>
                    <p
                        v-if="sampaiFormatted"
                        class="mt-0.5 text-sm text-muted-foreground"
                    >
                        Berlaku sampai {{ sampaiFormatted }}
                    </p>
                </div>
            </div>

            <a
                v-if="waUpgradeUrl && tier_efektif !== 'bisnis'"
                :href="waUpgradeUrl"
                target="_blank"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-primary-foreground transition-transform hover:scale-[1.02] active:scale-95"
            >
                <MessageCircle class="h-4 w-4" />
                Upgrade via WhatsApp
            </a>
        </div>

        <!-- Perbandingan tier -->
        <div
            class="overflow-hidden rounded-2xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
        >
            <div class="border-b border-sidebar-border/70 p-5 dark:border-sidebar-border">
                <h2 class="flex items-center gap-2 font-bold">
                    <Sparkles class="h-4 w-4 text-primary" />
                    Pilihan Paket
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 dark:border-sidebar-border">
                            <th class="p-4 text-left font-semibold">Paket</th>
                            <th
                                v-for="tier in tiers"
                                :key="tier.key"
                                class="p-4 text-center font-semibold"
                                :class="
                                    tier.key === tier_efektif
                                        ? 'text-primary'
                                        : ''
                                "
                            >
                                {{ tier.label }}
                                <div
                                    v-if="tier.key === tier_efektif"
                                    class="mt-1 text-[10px] font-bold tracking-wide text-primary uppercase"
                                >
                                    Aktif
                                </div>
                            </th>
                        </tr>
                        <tr class="border-b border-sidebar-border/70 dark:border-sidebar-border">
                            <td class="p-4 text-muted-foreground">Harga / bulan</td>
                            <td
                                v-for="tier in tiers"
                                :key="tier.key"
                                class="p-4 text-center font-bold tabular-nums"
                            >
                                {{
                                    tier.harga === 0
                                        ? 'Gratis'
                                        : formatRupiah(tier.harga)
                                }}
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in fiturRows"
                            :key="row.label"
                            class="border-b border-sidebar-border/50 last:border-0 dark:border-sidebar-border/50"
                        >
                            <td class="p-4">{{ row.label }}</td>
                            <td
                                v-for="tier in tiers"
                                :key="tier.key"
                                class="p-4 text-center"
                            >
                                <Check
                                    v-if="urutanTier(tier.key) >= urutanTier(row.tierMin)"
                                    class="mx-auto h-4 w-4 text-emerald-600 dark:text-emerald-400"
                                />
                                <X
                                    v-else
                                    class="mx-auto h-4 w-4 text-muted-foreground/40"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="p-4 text-xs text-muted-foreground">
                Fitur lain (kasir, stok, produk, pesanan online, promo, dan
                analisis penjualan/stok/pelanggan) tersedia di semua paket.
            </p>
        </div>

        <!-- Riwayat pembayaran -->
        <div
            v-if="riwayat.length > 0"
            class="rounded-2xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
        >
            <h2 class="font-bold">Riwayat Pembayaran</h2>
            <div class="mt-3 divide-y divide-sidebar-border/50 dark:divide-sidebar-border/50">
                <div
                    v-for="(r, i) in riwayat"
                    :key="i"
                    class="flex items-center justify-between gap-4 py-3 text-sm"
                >
                    <div>
                        <p class="font-medium capitalize">
                            {{ r.tier }}
                            <span class="text-muted-foreground">
                                · {{ metodeLabel[r.metode] ?? r.metode }}
                            </span>
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ r.periode_mulai }} → {{ r.periode_sampai }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold tabular-nums">
                            {{ formatRupiah(r.nominal) }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ r.tanggal }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
