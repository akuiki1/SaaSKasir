<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    ArrowDownRight,
    ArrowUpRight,
    Banknote,
    Building2,
    CalendarPlus,
    Crown,
    LineChart,
    ShoppingCart,
    Store,
    UsersRound,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { formatCompact, formatNumber, formatRupiah } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard CEO', href: '/ceo/dashboard' }],
    },
});

interface TrendPoint {
    date: string;
    label: string;
    gmv: number;
    transaksi: number;
}

interface PertumbuhanPoint {
    label: string;
    baru: number;
    kumulatif: number;
}

interface TopToko {
    nama: string;
    slug: string | null;
    tier: string;
    omzet: number;
    transaksi: number;
}

interface TokoBaru {
    nama: string;
    slug: string | null;
    tier: string;
    status: string;
    tanggal: string;
}

const props = defineProps<{
    ceo_name: string;
    today_label: string;
    toko_stats: {
        total: number;
        aktif: number;
        nonaktif: number;
        baru_bulan_ini: number;
    };
    user_stats: { total: number; admin: number; kasir: number };
    langganan_stats: {
        mrr: number;
        berbayar: number;
        distribusi: { tier: string; label: string; jumlah: number }[];
    };
    gmv_stats: Record<
        'hari_ini' | 'kemarin' | 'bulan_ini' | 'bulan_lalu',
        { gmv: number; transaksi: number }
    >;
    trend: TrendPoint[];
    pertumbuhan_toko: PertumbuhanPoint[];
    top_toko: TopToko[];
    toko_terbaru: TokoBaru[];
}>();

function deltaPct(current: number, previous: number): number | null {
    if (previous === 0) {
        return null;
    }

    return Math.round(((current - previous) / Math.abs(previous)) * 1000) / 10;
}

// --- Kartu KPI platform ---
const kpiCards = computed(() => [
    {
        label: 'Total Toko',
        value: formatNumber(props.toko_stats.total, 0),
        subtitle: `${props.toko_stats.aktif} aktif · ${props.toko_stats.nonaktif} nonaktif`,
        badge:
            props.toko_stats.baru_bulan_ini > 0
                ? `+${props.toko_stats.baru_bulan_ini} bulan ini`
                : undefined,
        icon: Store,
        tint: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300',
    },
    {
        label: 'GMV Bulan Ini',
        value: formatRupiah(props.gmv_stats.bulan_ini.gmv),
        delta: deltaPct(props.gmv_stats.bulan_ini.gmv, props.gmv_stats.bulan_lalu.gmv),
        deltaLabel: 'vs bulan lalu',
        icon: LineChart,
        tint: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
    },
    {
        label: 'Transaksi Hari Ini',
        value: formatNumber(props.gmv_stats.hari_ini.transaksi, 0),
        subtitle: `GMV ${formatRupiah(props.gmv_stats.hari_ini.gmv)}`,
        delta: deltaPct(
            props.gmv_stats.hari_ini.transaksi,
            props.gmv_stats.kemarin.transaksi,
        ),
        deltaLabel: 'vs kemarin',
        icon: ShoppingCart,
        tint: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300',
    },
    {
        label: 'MRR (Estimasi)',
        value: formatRupiah(props.langganan_stats.mrr),
        subtitle: `${props.langganan_stats.berbayar} toko berbayar`,
        icon: Banknote,
        tint: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
    },
]);

// --- Grafik tren 14 hari (pola sama dengan dashboard admin) ---
const metric = ref<'gmv' | 'transaksi'>('gmv');
const isGmv = computed(() => metric.value === 'gmv');

const W = 720;
const H = 250;
const PAD = { left: 46, right: 18, top: 24, bottom: 34 };
const plotW = W - PAD.left - PAD.right;
const plotH = H - PAD.top - PAD.bottom;

function niceCeil(value: number): number {
    if (value <= 0) {
        return 1;
    }

    const mag = Math.pow(10, Math.floor(Math.log10(value)));
    const norm = value / mag;
    const nice = norm <= 1 ? 1 : norm <= 2 ? 2 : norm <= 2.5 ? 2.5 : norm <= 5 ? 5 : 10;

    return nice * mag;
}

function metricValue(point: TrendPoint): number {
    return isGmv.value ? point.gmv : point.transaksi;
}

const chart = computed(() => {
    const data = props.trend;
    const max = niceCeil(Math.max(...data.map(metricValue), 0));
    const stepX = data.length > 1 ? plotW / (data.length - 1) : 0;
    const baseY = PAD.top + plotH;

    const points = data.map((point, i) => {
        const value = metricValue(point);
        const valueText = isGmv.value
            ? formatCompact(value)
            : formatNumber(value, 0);

        return {
            key: point.date,
            x: PAD.left + stepX * i,
            y: PAD.top + plotH * (1 - value / max),
            label: point.label,
            valueText,
            isLast: i === data.length - 1,
            showXLabel: i % 2 === data.length % 2 || i === data.length - 1,
            badgeW: Math.max(34, valueText.length * 7 + 12),
        };
    });

    const linePath = points
        .map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x.toFixed(1)},${p.y.toFixed(1)}`)
        .join(' ');
    const areaPath = points.length
        ? `M${points[0].x.toFixed(1)},${baseY} ${points.map((p) => `L${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ')} L${points[points.length - 1].x.toFixed(1)},${baseY} Z`
        : '';

    const ticks = [0, 1, 2, 3, 4].map((i) => ({
        y: PAD.top + plotH * (1 - i / 4),
        label: isGmv.value
            ? formatCompact((max * i) / 4)
            : formatNumber(Math.round((max * i) / 4), 0),
    }));

    return { points, linePath, areaPath, ticks, baseY };
});

// --- Distribusi tier: segmented bar ---
const TIER_COLORS: Record<string, string> = {
    gratis: 'bg-slate-300 dark:bg-zinc-600',
    warung: 'bg-sky-500',
    bisnis: 'bg-violet-500',
};

const distribusiTotal = computed(() =>
    props.langganan_stats.distribusi.reduce((sum, d) => sum + d.jumlah, 0),
);

// --- Pertumbuhan toko: bar sederhana berbasis div ---
const maxBaru = computed(() =>
    Math.max(...props.pertumbuhan_toko.map((p) => p.baru), 1),
);

const TIER_CHIP: Record<string, string> = {
    gratis: 'border-slate-300/50 bg-slate-100 text-slate-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300',
    warung: 'border-sky-500/20 bg-sky-500/10 text-sky-600 dark:text-sky-400',
    bisnis: 'border-violet-500/20 bg-violet-500/10 text-violet-600 dark:text-violet-400',
};

function deltaTone(delta: number | null): string {
    if (delta === null || delta === 0) {
        return 'text-slate-400 dark:text-slate-500';
    }

    return delta > 0
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-rose-600 dark:text-rose-400';
}
</script>

<template>
    <Head title="Dashboard CEO" />

    <div class="flex h-full flex-1 flex-col gap-6 bg-slate-50 p-6 text-slate-950 dark:bg-zinc-950 dark:text-slate-100">
        <!-- Sapaan -->
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <Crown class="h-5 w-5 text-amber-500" />
                <h1 class="text-2xl font-extrabold tracking-tight">Pantauan Platform SiKasir</h1>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ today_label }} · Kesehatan seluruh sistem dalam satu layar, {{ ceo_name }}.
            </p>
        </div>

        <!-- KPI platform -->
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="card in kpiCards"
                :key="card.label"
                class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                        <p class="mt-2 truncate text-2xl font-bold tracking-tight">{{ card.value }}</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg" :class="card.tint">
                        <component :is="card.icon" class="h-5 w-5" />
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-medium">
                    <span v-if="card.subtitle" class="text-slate-500 dark:text-slate-400">{{ card.subtitle }}</span>
                    <span v-if="card.delta !== undefined" class="flex items-center gap-1" :class="deltaTone(card.delta ?? null)">
                        <template v-if="card.delta === null">—</template>
                        <template v-else>
                            <ArrowUpRight v-if="card.delta > 0" class="h-3.5 w-3.5" />
                            <ArrowDownRight v-else-if="card.delta < 0" class="h-3.5 w-3.5" />
                            {{ Math.abs(card.delta) }}%
                            <span class="text-slate-400 dark:text-slate-500">{{ card.deltaLabel }}</span>
                        </template>
                    </span>
                    <span
                        v-if="card.badge"
                        class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"
                    >
                        {{ card.badge }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Tren platform + Langganan & pengguna -->
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
            <!-- Tren 14 hari -->
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4 dark:border-zinc-800">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Tren Platform 14 Hari</h2>
                    <select
                        v-model="metric"
                        class="h-8 rounded-lg border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-600 outline-none transition focus:border-sky-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-slate-300"
                    >
                        <option value="gmv">GMV (Omzet Semua Toko)</option>
                        <option value="transaksi">Jumlah Transaksi</option>
                    </select>
                </div>

                <div class="mt-3">
                    <svg :viewBox="`0 0 ${W} ${H}`" class="h-auto w-full" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Grafik tren platform 14 hari">
                        <defs>
                            <linearGradient id="ceoTrendArea" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.22" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                            </linearGradient>
                        </defs>

                        <g v-for="(tick, i) in chart.ticks" :key="`tick-${i}`">
                            <line :x1="PAD.left" :y1="tick.y" :x2="W - PAD.right" :y2="tick.y" class="stroke-slate-100 dark:stroke-zinc-800" stroke-width="1" />
                            <text :x="PAD.left - 8" :y="tick.y + 3" text-anchor="end" class="fill-slate-400 text-[10px] dark:fill-slate-500">{{ tick.label }}</text>
                        </g>

                        <path :d="chart.areaPath" fill="url(#ceoTrendArea)" />
                        <path :d="chart.linePath" fill="none" class="stroke-emerald-500" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />

                        <g v-for="point in chart.points" :key="point.key">
                            <circle :cx="point.x" :cy="point.y" :r="point.isLast ? 4.5 : 3" class="fill-white stroke-emerald-500" stroke-width="2" />

                            <template v-if="point.isLast">
                                <rect :x="point.x - point.badgeW / 2" :y="point.y - 27" :width="point.badgeW" height="18" rx="5" class="fill-emerald-500" />
                                <text :x="point.x" :y="point.y - 14" text-anchor="middle" class="fill-white text-[10px] font-bold">{{ point.valueText }}</text>
                            </template>

                            <text v-if="point.showXLabel" :x="point.x" :y="chart.baseY + 18" text-anchor="middle" class="fill-slate-400 text-[10px] dark:fill-slate-500">{{ point.label }}</text>
                        </g>
                    </svg>
                </div>
            </section>

            <!-- Langganan & pengguna -->
            <section class="flex flex-col gap-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Distribusi Tier</h2>

                    <div v-if="distribusiTotal > 0" class="mt-4 flex h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-zinc-800">
                        <div
                            v-for="d in langganan_stats.distribusi.filter((x) => x.jumlah > 0)"
                            :key="d.tier"
                            class="h-full"
                            :class="TIER_COLORS[d.tier] ?? 'bg-slate-400'"
                            :style="{ width: `${(d.jumlah / distribusiTotal) * 100}%` }"
                            :title="`${d.label}: ${d.jumlah} toko`"
                        ></div>
                    </div>

                    <div class="mt-4 space-y-2.5">
                        <div v-for="d in langganan_stats.distribusi" :key="d.tier" class="flex items-center justify-between gap-3 text-sm">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                <span class="h-2.5 w-2.5 rounded-full" :class="TIER_COLORS[d.tier] ?? 'bg-slate-400'"></span>
                                {{ d.label }}
                            </span>
                            <span class="font-bold tabular-nums">{{ d.jumlah }} <span class="font-normal text-slate-400">toko</span></span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                        <UsersRound class="h-4 w-4" /> Pengguna Toko
                    </h2>
                    <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-lg bg-slate-50 p-3 dark:bg-zinc-800/50">
                            <p class="text-xl font-bold tabular-nums">{{ formatNumber(user_stats.total, 0) }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Total</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3 dark:bg-zinc-800/50">
                            <p class="text-xl font-bold tabular-nums">{{ formatNumber(user_stats.admin, 0) }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Admin</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3 dark:bg-zinc-800/50">
                            <p class="text-xl font-bold tabular-nums">{{ formatNumber(user_stats.kasir, 0) }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Kasir</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Pertumbuhan toko + Top toko -->
        <div class="grid gap-4 xl:grid-cols-2">
            <!-- Pertumbuhan toko 6 bulan -->
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4 dark:border-zinc-800">
                    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                        <Building2 class="h-4 w-4" /> Pertumbuhan Toko
                    </h2>
                    <span class="text-xs text-slate-400 dark:text-slate-500">6 bulan terakhir</span>
                </div>

                <div class="mt-5 flex items-end justify-between gap-3" style="height: 150px">
                    <div v-for="p in pertumbuhan_toko" :key="p.label" class="flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                        <span class="text-xs font-bold tabular-nums text-emerald-600 dark:text-emerald-400">+{{ p.baru }}</span>
                        <div
                            class="w-full max-w-12 rounded-t-lg bg-gradient-to-t from-emerald-500 to-teal-400 transition-all"
                            :style="{ height: `${Math.max((p.baru / maxBaru) * 100, 4)}px` }"
                        ></div>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ p.label }}</span>
                        <span class="text-[10px] tabular-nums text-slate-400 dark:text-slate-500">Σ {{ p.kumulatif }}</span>
                    </div>
                </div>
            </section>

            <!-- Top toko bulan ini -->
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4 dark:border-zinc-800">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Top Toko Bulan Ini</h2>
                    <span class="text-xs text-slate-400 dark:text-slate-500">berdasarkan omzet</span>
                </div>

                <div v-if="top_toko.length" class="mt-2 divide-y divide-slate-100 dark:divide-zinc-800">
                    <div v-for="(toko, i) in top_toko" :key="toko.nama + i" class="flex items-center gap-3 py-3">
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                            :class="i === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-slate-400'"
                        >
                            {{ i + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ toko.nama }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ toko.transaksi }} transaksi</p>
                        </div>
                        <span
                            class="inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase"
                            :class="TIER_CHIP[toko.tier] ?? TIER_CHIP.gratis"
                        >
                            {{ toko.tier }}
                        </span>
                        <span class="shrink-0 text-sm font-bold tabular-nums">{{ formatRupiah(toko.omzet) }}</span>
                    </div>
                </div>
                <p v-else class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada transaksi bulan ini.</p>
            </section>
        </div>

        <!-- Toko terbaru -->
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-2 border-b border-slate-200 pb-4 dark:border-zinc-800">
                <CalendarPlus class="h-4 w-4 text-slate-500" />
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Pendaftaran Terbaru</h2>
            </div>

            <div v-if="toko_terbaru.length" class="mt-2 divide-y divide-slate-100 dark:divide-zinc-800">
                <div v-for="toko in toko_terbaru" :key="toko.nama + toko.tanggal" class="flex items-center gap-3 py-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                        <Store class="h-4 w-4" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ toko.nama }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">/toko/{{ toko.slug ?? '-' }}</p>
                    </div>
                    <span
                        class="inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase"
                        :class="TIER_CHIP[toko.tier] ?? TIER_CHIP.gratis"
                    >
                        {{ toko.tier }}
                    </span>
                    <span
                        class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        :class="toko.status === 'aktif' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300'"
                    >
                        {{ toko.status }}
                    </span>
                    <span class="shrink-0 text-xs text-slate-400 dark:text-slate-500">{{ toko.tanggal }}</span>
                </div>
            </div>
            <p v-else class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada toko terdaftar.</p>
        </section>
    </div>
</template>
