<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowRight,
    CreditCard,
    ShieldCheck,
    Store,
    UserCog,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { formatRupiah } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard Super Admin', href: '/superadmin/dashboard' }],
    },
});

interface SegeraBerakhir {
    id_toko: number;
    nama: string;
    tier: string;
    sampai: string | null;
    sisa_hari: number;
}

interface Pembayaran {
    toko: string;
    tier: string;
    nominal: number;
    metode: string;
    tanggal: string;
}

interface TokoBaru {
    id_toko: number;
    nama: string;
    slug: string | null;
    status: string;
    tier: string;
    tanggal: string;
}

const props = defineProps<{
    nama: string;
    today_label: string;
    stats: {
        total_toko: number;
        toko_aktif: number;
        toko_nonaktif: number;
        total_admin: number;
        total_kasir: number;
        langganan_berbayar: number;
        segera_berakhir: number;
    };
    segera_berakhir: SegeraBerakhir[];
    pembayaran_terbaru: Pembayaran[];
    toko_terbaru: TokoBaru[];
}>();

const kpiCards = computed(() => [
    {
        label: 'Total Toko',
        value: `${props.stats.total_toko}`,
        subtitle: `${props.stats.toko_aktif} aktif · ${props.stats.toko_nonaktif} nonaktif`,
        icon: Store,
        href: '/superadmin/toko',
        tint: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300',
    },
    {
        label: 'Akun Admin',
        value: `${props.stats.total_admin}`,
        subtitle: `${props.stats.total_kasir} kasir di semua toko`,
        icon: UserCog,
        href: '/superadmin/admins',
        tint: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
    },
    {
        label: 'Langganan Berbayar',
        value: `${props.stats.langganan_berbayar}`,
        subtitle: 'toko dengan tier aktif',
        icon: Wallet,
        href: '/superadmin/toko?tier=warung',
        tint: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300',
    },
    {
        label: 'Segera Berakhir',
        value: `${props.stats.segera_berakhir}`,
        subtitle: 'langganan ≤ 14 hari lagi',
        icon: AlertTriangle,
        href: '/superadmin/toko',
        tint: props.stats.segera_berakhir > 0
            ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300'
            : 'bg-slate-50 text-slate-500 dark:bg-zinc-800 dark:text-slate-400',
    },
]);

function sisaHariLabel(hari: number): string {
    if (hari < 0) {
        return `lewat ${Math.abs(hari)} hari`;
    }

    if (hari === 0) {
        return 'hari ini';
    }

    return `${hari} hari lagi`;
}
</script>

<template>
    <Head title="Dashboard Super Admin" />

    <div class="flex h-full flex-1 flex-col gap-6 bg-slate-50 p-6 text-slate-950 dark:bg-zinc-950 dark:text-slate-100">
        <!-- Sapaan -->
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <ShieldCheck class="h-5 w-5 text-emerald-500" />
                <h1 class="text-2xl font-extrabold tracking-tight">Operasional Platform</h1>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ today_label }} · Yang perlu ditindak hari ini, {{ nama }}.
            </p>
        </div>

        <!-- KPI (semuanya bisa diklik menuju halaman kelola) -->
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <Link
                v-for="card in kpiCards"
                :key="card.label"
                :href="card.href"
                class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight">{{ card.value }}</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg" :class="card.tint">
                        <component :is="card.icon" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between text-xs font-medium text-slate-500 dark:text-slate-400">
                    <span>{{ card.subtitle }}</span>
                    <ArrowRight class="h-3.5 w-3.5 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-500 dark:text-zinc-700 dark:group-hover:text-slate-400" />
                </div>
            </Link>
        </div>

        <!-- Langganan segera berakhir + pembayaran terbaru -->
        <div class="grid gap-4 xl:grid-cols-2">
            <!-- Segera berakhir -->
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4 dark:border-zinc-800">
                    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                        <AlertTriangle class="h-4 w-4 text-amber-500" /> Langganan Segera Berakhir
                    </h2>
                    <Link href="/superadmin/toko" class="text-xs font-semibold text-sky-600 transition hover:text-sky-700 dark:text-sky-400">Kelola Toko</Link>
                </div>

                <div v-if="segera_berakhir.length" class="mt-2 divide-y divide-slate-100 dark:divide-zinc-800">
                    <div v-for="toko in segera_berakhir" :key="toko.id_toko" class="flex items-center gap-3 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ toko.nama }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">
                                <span class="uppercase">{{ toko.tier }}</span> · sampai {{ toko.sampai ?? '-' }}
                            </p>
                        </div>
                        <span
                            class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-[11px] font-bold"
                            :class="toko.sisa_hari <= 3
                                ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300'
                                : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'"
                        >
                            {{ sisaHariLabel(toko.sisa_hari) }}
                        </span>
                    </div>
                </div>
                <div v-else class="mt-4 flex flex-col items-center gap-2 rounded-lg border border-dashed border-slate-200 py-10 text-center dark:border-zinc-700">
                    <ShieldCheck class="h-8 w-8 text-emerald-500" />
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Tidak ada langganan yang kritis.</p>
                </div>
            </section>

            <!-- Pembayaran terbaru -->
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2 border-b border-slate-200 pb-4 dark:border-zinc-800">
                    <CreditCard class="h-4 w-4 text-slate-500" />
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Pembayaran Terbaru</h2>
                </div>

                <div v-if="pembayaran_terbaru.length" class="mt-2 divide-y divide-slate-100 dark:divide-zinc-800">
                    <div v-for="(bayar, i) in pembayaran_terbaru" :key="i" class="flex items-center gap-3 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ bayar.toko }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">
                                <span class="uppercase">{{ bayar.tier }}</span> · {{ bayar.metode }} · {{ bayar.tanggal }}
                            </p>
                        </div>
                        <span class="shrink-0 text-sm font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                            {{ formatRupiah(bayar.nominal) }}
                        </span>
                    </div>
                </div>
                <p v-else class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada pembayaran tercatat.</p>
            </section>
        </div>

        <!-- Toko terbaru -->
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4 dark:border-zinc-800">
                <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                    <Store class="h-4 w-4 text-slate-500" /> Toko Baru Mendaftar
                </h2>
                <Link href="/superadmin/toko" class="text-xs font-semibold text-sky-600 transition hover:text-sky-700 dark:text-sky-400">Lihat Semua</Link>
            </div>

            <div v-if="toko_terbaru.length" class="mt-2 divide-y divide-slate-100 dark:divide-zinc-800">
                <div v-for="toko in toko_terbaru" :key="toko.id_toko" class="flex items-center gap-3 py-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                        <Store class="h-4 w-4" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ toko.nama }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">/toko/{{ toko.slug ?? '-' }} · daftar {{ toko.tanggal }}</p>
                    </div>
                    <span
                        class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        :class="toko.status === 'aktif' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300'"
                    >
                        {{ toko.status }}
                    </span>
                    <span class="inline-flex shrink-0 items-center rounded-full border border-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-500 dark:border-zinc-700 dark:text-slate-400">
                        {{ toko.tier }}
                    </span>
                </div>
            </div>
            <p v-else class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada toko terdaftar.</p>
        </section>
    </div>
</template>
