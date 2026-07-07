<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    Ban,
    CalendarClock,
    CheckCircle2,
    CreditCard,
    ExternalLink,
    Plus,
    Power,
    Save,
    Search,
    Store,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import BodyTeleport from '@/components/BodyTeleport.vue';
import Pagination from '@/components/Pagination.vue';
import { formatRupiah } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Kelola Toko', href: '/superadmin/toko' }],
    },
});

interface TokoItem {
    id_toko: number;
    nama: string;
    slug: string | null;
    status: string;
    tier: string;
    tier_efektif: string;
    langganan_sampai: string | null;
    users_count: number;
    admin: { name: string; email: string } | null;
    trx_30hari: number;
    omzet_30hari: number;
    created_at: string | null;
}

interface Tier {
    key: string;
    label: string;
    harga: number;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

const props = defineProps<{
    tokos: Paginator<TokoItem>;
    stats: { total: number; aktif: number; nonaktif: number };
    tiers: Tier[];
    filters: { search: string; status: string; tier: string; per_page: number };
}>();

// --- Search & filter server-side (pola sama dengan admin/Users) ---
const searchQuery = ref(props.filters.search ?? '');
const filterStatus = ref(props.filters.status ?? '');
const filterTier = ref(props.filters.tier ?? '');

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(searchQuery, (value) => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => reload({ search: value, page: 1 }), 350);
});
watch(filterStatus, (value) => reload({ status: value, page: 1 }));
watch(filterTier, (value) => reload({ tier: value, page: 1 }));

type QueryValue = string | number;

function reload(overrides: Record<string, QueryValue> = {}): void {
    const params: Record<string, QueryValue | undefined> = {
        search: searchQuery.value || undefined,
        status: filterStatus.value || undefined,
        tier: filterTier.value || undefined,
        per_page: props.filters.per_page,
        ...overrides,
    };

    const cleaned: Record<string, QueryValue> = {};
    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== '') {
            cleaned[key] = value;
        }
    });

    router.get('/superadmin/toko', cleaned, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const visiblePages = computed(() => {
    const pages: number[] = [];
    const total = props.tokos.last_page;
    const current = props.tokos.current_page;

    if (total <= 7) {
        for (let i = 1; i <= total; i++) {
            pages.push(i);
        }
    } else {
        pages.push(1);
        if (current > 3) {
            pages.push(-1);
        }
        for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
            pages.push(i);
        }
        if (current < total - 2) {
            pages.push(-1);
        }
        pages.push(total);
    }

    return pages;
});

// --- Tambah toko baru (onboarding high-touch oleh superadmin) ---
const showTambah = ref(false);

const tambahForm = useForm({
    nama_toko: '',
    nama_pemilik: '',
    email: '',
    whatsapp: '',
    password: '',
    password_confirmation: '',
});

function submitTambah() {
    tambahForm.post('/superadmin/toko', {
        onSuccess: () => {
            showTambah.value = false;
            tambahForm.reset();
        },
    });
}

// --- Perpanjang langganan ---
const langgananToko = ref<TokoItem | null>(null);

const tierBerbayar = computed(() => props.tiers.filter((t) => t.harga > 0));

const langgananForm = useForm({
    tier: '',
    bulan: 1,
    nominal: null as number | null,
    catatan: '',
});

function openLangganan(toko: TokoItem) {
    langgananToko.value = toko;
    langgananForm.reset();
    langgananForm.clearErrors();
    langgananForm.tier =
        toko.tier !== 'gratis' ? toko.tier : (tierBerbayar.value[0]?.key ?? '');
}

const estimasiNominal = computed(() => {
    const tier = props.tiers.find((t) => t.key === langgananForm.tier);

    return (tier?.harga ?? 0) * (Number(langgananForm.bulan) || 0);
});

function submitLangganan() {
    if (!langgananToko.value) {
        return;
    }

    langgananForm.post(`/superadmin/toko/${langgananToko.value.id_toko}/langganan`, {
        preserveScroll: true,
        onSuccess: () => {
            langgananToko.value = null;
        },
    });
}

// --- Aktif / nonaktifkan toko ---
function toggleStatus(toko: TokoItem) {
    const keAktif = toko.status !== 'aktif';
    const tanya = keAktif
        ? `Aktifkan kembali toko "${toko.nama}"?`
        : `Nonaktifkan toko "${toko.nama}"? Storefront publiknya tidak bisa diakses selama nonaktif.`;

    if (confirm(tanya)) {
        router.put(
            `/superadmin/toko/${toko.id_toko}/status`,
            { status: keAktif ? 'aktif' : 'nonaktif' },
            { preserveScroll: true },
        );
    }
}

const TIER_CHIP: Record<string, string> = {
    gratis: 'border-slate-300/50 bg-slate-100 text-slate-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300',
    warung: 'border-sky-500/20 bg-sky-500/10 text-sky-600 dark:text-sky-400',
    bisnis: 'border-violet-500/20 bg-violet-500/10 text-violet-600 dark:text-violet-400',
};

function formatDate(dateString: string | null): string {
    if (!dateString) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(dateString));
}
</script>

<template>
    <Head title="Kelola Toko - Super Admin" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">Kelola Toko</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Semua tenant SiKasir: status, langganan, dan onboarding toko baru.
                </p>
            </div>

            <button
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition-colors hover:bg-emerald-500"
                @click="showTambah = true"
            >
                <Plus class="h-4 w-4" />
                Daftarkan Toko Baru
            </button>
        </div>

        <!-- Statistik ringkas -->
        <div class="grid gap-4 md:grid-cols-3">
            <div class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border">
                <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3 text-emerald-600 dark:text-emerald-400">
                    <Store class="h-6 w-6" />
                </div>
                <div>
                    <span class="text-xs font-medium text-muted-foreground">Total Toko</span>
                    <h3 class="mt-0.5 text-xl font-bold">{{ stats.total }} Toko</h3>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border">
                <div class="rounded-lg border border-sky-500/20 bg-sky-500/10 p-3 text-sky-600 dark:text-sky-400">
                    <CheckCircle2 class="h-6 w-6" />
                </div>
                <div>
                    <span class="text-xs font-medium text-muted-foreground">Aktif</span>
                    <h3 class="mt-0.5 text-xl font-bold">{{ stats.aktif }} Toko</h3>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border">
                <div class="rounded-lg border border-rose-500/20 bg-rose-500/10 p-3 text-rose-600 dark:text-rose-400">
                    <Ban class="h-6 w-6" />
                </div>
                <div>
                    <span class="text-xs font-medium text-muted-foreground">Nonaktif</span>
                    <h3 class="mt-0.5 text-xl font-bold">{{ stats.nonaktif }} Toko</h3>
                </div>
            </div>
        </div>

        <!-- Tabel toko -->
        <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border">
            <div class="flex flex-col gap-4 border-b border-sidebar-border/70 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-sidebar-border">
                <div class="relative max-w-md flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama toko atau slug..."
                        class="w-full rounded-lg border border-sidebar-border/70 bg-background py-2 pl-9 pr-4 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                    />
                </div>

                <div class="flex gap-2">
                    <select
                        v-model="filterStatus"
                        class="rounded-lg border border-sidebar-border/70 bg-background px-3 py-2 text-sm font-medium dark:border-sidebar-border"
                    >
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                    <select
                        v-model="filterTier"
                        class="rounded-lg border border-sidebar-border/70 bg-background px-3 py-2 text-sm font-medium dark:border-sidebar-border"
                    >
                        <option value="">Semua Tier</option>
                        <option v-for="tier in tiers" :key="tier.key" :value="tier.key">{{ tier.label }}</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 bg-slate-50/50 dark:border-sidebar-border dark:bg-zinc-800/20">
                            <th class="px-6 py-4 font-semibold text-muted-foreground">Toko</th>
                            <th class="px-6 py-4 font-semibold text-muted-foreground">Admin Pemilik</th>
                            <th class="px-6 py-4 font-semibold text-muted-foreground">Langganan</th>
                            <th class="px-6 py-4 font-semibold text-muted-foreground">30 Hari Terakhir</th>
                            <th class="px-6 py-4 font-semibold text-muted-foreground">Status</th>
                            <th class="px-6 py-4 text-right font-semibold text-muted-foreground">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        <tr v-if="props.tokos.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                <Store class="mx-auto mb-3 h-10 w-10 opacity-30" />
                                <p class="font-medium">
                                    {{ searchQuery || filterStatus || filterTier ? 'Tidak ada toko yang sesuai filter.' : 'Belum ada toko terdaftar.' }}
                                </p>
                            </td>
                        </tr>
                        <tr
                            v-for="toko in props.tokos.data"
                            :key="toko.id_toko"
                            class="transition-colors hover:bg-slate-50/50 dark:hover:bg-zinc-800/10"
                        >
                            <td class="px-6 py-4">
                                <p class="font-semibold text-foreground">{{ toko.nama }}</p>
                                <a
                                    v-if="toko.slug"
                                    :href="`/toko/${toko.slug}`"
                                    target="_blank"
                                    rel="noopener"
                                    class="mt-0.5 inline-flex items-center gap-1 text-xs text-muted-foreground transition hover:text-emerald-600"
                                    :title="`Buka storefront ${toko.nama}`"
                                >
                                    /toko/{{ toko.slug }}
                                    <ExternalLink class="h-3 w-3" />
                                </a>
                                <p class="mt-0.5 text-xs text-muted-foreground">daftar {{ formatDate(toko.created_at) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <template v-if="toko.admin">
                                    <p class="font-medium text-foreground">{{ toko.admin.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ toko.admin.email }}</p>
                                </template>
                                <span v-else class="inline-flex items-center gap-1 text-xs font-semibold text-rose-500">
                                    <AlertCircle class="h-3.5 w-3.5" /> Tanpa admin
                                </span>
                                <p class="mt-0.5 text-xs text-muted-foreground">{{ toko.users_count }} akun</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider"
                                    :class="TIER_CHIP[toko.tier_efektif] ?? TIER_CHIP.gratis"
                                >
                                    {{ toko.tier_efektif }}
                                </span>
                                <p v-if="toko.langganan_sampai" class="mt-1 text-xs text-muted-foreground">
                                    s.d. {{ formatDate(toko.langganan_sampai) }}
                                    <span v-if="toko.tier !== toko.tier_efektif" class="text-rose-500">(kedaluwarsa)</span>
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold tabular-nums">{{ toko.trx_30hari }} trx</p>
                                <p class="text-xs tabular-nums text-muted-foreground">{{ formatRupiah(toko.omzet_30hari) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="toko.status === 'aktif'
                                        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                        : 'bg-rose-500/10 text-rose-600 dark:text-rose-400'"
                                >
                                    {{ toko.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex justify-end gap-2">
                                    <button
                                        class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-slate-100 hover:text-emerald-600 dark:hover:bg-zinc-800"
                                        title="Catat perpanjangan langganan"
                                        @click="openLangganan(toko)"
                                    >
                                        <CreditCard class="h-4 w-4" />
                                    </button>
                                    <button
                                        class="rounded-lg p-1.5 text-muted-foreground transition-colors dark:hover:bg-zinc-800"
                                        :class="toko.status === 'aktif' ? 'hover:bg-rose-50 hover:text-rose-600' : 'hover:bg-emerald-50 hover:text-emerald-600'"
                                        :title="toko.status === 'aktif' ? 'Nonaktifkan toko' : 'Aktifkan toko'"
                                        @click="toggleStatus(toko)"
                                    >
                                        <Power class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                :current-page="props.tokos.current_page"
                :total-pages="props.tokos.last_page"
                :total-items="props.tokos.total"
                :start-index="props.tokos.from ?? 0"
                :end-index="props.tokos.to ?? 0"
                :per-page="props.tokos.per_page"
                :visible-pages="visiblePages"
                @update:current-page="(page: number) => reload({ page })"
                @update:per-page="(value: number) => reload({ per_page: value, page: 1 })"
            />
        </div>
    </div>

    <!-- Modal: tambah toko baru -->
    <BodyTeleport>
        <div
            v-if="showTambah"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @click.self="showTambah = false"
        >
            <div class="w-full max-w-md rounded-2xl border border-sidebar-border/70 bg-card p-6 shadow-2xl dark:border-sidebar-border" style="max-height: 90vh; overflow-y: auto">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-bold">Daftarkan Toko Baru</h2>
                    <button class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-slate-100 dark:hover:bg-zinc-800" aria-label="Tutup" @click="showTambah = false">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <p class="mb-4 rounded-lg bg-emerald-50 p-3 text-xs text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
                    Jalur onboarding high-touch: toko + akun admin pemiliknya dibuat sekaligus,
                    sama seperti registrasi mandiri.
                </p>

                <form class="flex flex-col gap-4" @submit.prevent="submitTambah">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium" for="tambah-nama-toko">Nama Toko</label>
                        <input
                            id="tambah-nama-toko"
                            v-model="tambahForm.nama_toko"
                            type="text"
                            placeholder="Warung Berkah Jaya"
                            class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                            :class="{ 'border-rose-500': tambahForm.errors.nama_toko }"
                        />
                        <p v-if="tambahForm.errors.nama_toko" class="mt-1 flex items-center gap-1 text-xs text-rose-600">
                            <AlertCircle class="h-3 w-3" />{{ tambahForm.errors.nama_toko }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium" for="tambah-pemilik">Nama Pemilik</label>
                        <input
                            id="tambah-pemilik"
                            v-model="tambahForm.nama_pemilik"
                            type="text"
                            placeholder="Nama lengkap pemilik"
                            class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                            :class="{ 'border-rose-500': tambahForm.errors.nama_pemilik }"
                        />
                        <p v-if="tambahForm.errors.nama_pemilik" class="mt-1 flex items-center gap-1 text-xs text-rose-600">
                            <AlertCircle class="h-3 w-3" />{{ tambahForm.errors.nama_pemilik }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" for="tambah-email">Email Pemilik</label>
                            <input
                                id="tambah-email"
                                v-model="tambahForm.email"
                                type="email"
                                placeholder="pemilik@email.com"
                                class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                                :class="{ 'border-rose-500': tambahForm.errors.email }"
                            />
                            <p v-if="tambahForm.errors.email" class="mt-1 flex items-center gap-1 text-xs text-rose-600">
                                <AlertCircle class="h-3 w-3" />{{ tambahForm.errors.email }}
                            </p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" for="tambah-wa">No. WhatsApp</label>
                            <input
                                id="tambah-wa"
                                v-model="tambahForm.whatsapp"
                                type="text"
                                placeholder="08xxxxxxxxxx"
                                class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                                :class="{ 'border-rose-500': tambahForm.errors.whatsapp }"
                            />
                            <p v-if="tambahForm.errors.whatsapp" class="mt-1 flex items-center gap-1 text-xs text-rose-600">
                                <AlertCircle class="h-3 w-3" />{{ tambahForm.errors.whatsapp }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" for="tambah-password">Password</label>
                            <input
                                id="tambah-password"
                                v-model="tambahForm.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                                :class="{ 'border-rose-500': tambahForm.errors.password }"
                            />
                            <p v-if="tambahForm.errors.password" class="mt-1 flex items-center gap-1 text-xs text-rose-600">
                                <AlertCircle class="h-3 w-3" />{{ tambahForm.errors.password }}
                            </p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" for="tambah-password2">Konfirmasi Password</label>
                            <input
                                id="tambah-password2"
                                v-model="tambahForm.password_confirmation"
                                type="password"
                                placeholder="Ulangi password"
                                class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                            />
                        </div>
                    </div>

                    <div class="mt-2 flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-lg border border-sidebar-border/70 px-4 py-2 text-sm font-semibold transition hover:bg-slate-50 dark:border-sidebar-border dark:hover:bg-zinc-800"
                            @click="showTambah = false"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="tambahForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 disabled:opacity-60"
                        >
                            <Save class="h-4 w-4" />
                            Daftarkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </BodyTeleport>

    <!-- Modal: perpanjang langganan -->
    <BodyTeleport>
        <div
            v-if="langgananToko"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @click.self="langgananToko = null"
        >
            <div class="w-full max-w-md rounded-2xl border border-sidebar-border/70 bg-card p-6 shadow-2xl dark:border-sidebar-border" style="max-height: 90vh; overflow-y: auto">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold">Perpanjang Langganan</h2>
                        <p class="text-sm text-muted-foreground">{{ langgananToko.nama }}</p>
                    </div>
                    <button class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-slate-100 dark:hover:bg-zinc-800" aria-label="Tutup" @click="langgananToko = null">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form class="flex flex-col gap-4" @submit.prevent="submitLangganan">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium" for="langganan-tier">Tier</label>
                        <select
                            id="langganan-tier"
                            v-model="langgananForm.tier"
                            class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                            :class="{ 'border-rose-500': langgananForm.errors.tier }"
                        >
                            <option v-for="tier in tierBerbayar" :key="tier.key" :value="tier.key">
                                {{ tier.label }} — {{ formatRupiah(tier.harga) }}/bulan
                            </option>
                        </select>
                        <p v-if="langgananForm.errors.tier" class="mt-1 flex items-center gap-1 text-xs text-rose-600">
                            <AlertCircle class="h-3 w-3" />{{ langgananForm.errors.tier }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" for="langganan-bulan">Durasi (bulan)</label>
                            <input
                                id="langganan-bulan"
                                v-model.number="langgananForm.bulan"
                                type="number"
                                min="1"
                                max="24"
                                class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                                :class="{ 'border-rose-500': langgananForm.errors.bulan }"
                            />
                            <p v-if="langgananForm.errors.bulan" class="mt-1 flex items-center gap-1 text-xs text-rose-600">
                                <AlertCircle class="h-3 w-3" />{{ langgananForm.errors.bulan }}
                            </p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" for="langganan-nominal">Nominal (Rp)</label>
                            <input
                                id="langganan-nominal"
                                v-model.number="langgananForm.nominal"
                                type="number"
                                min="0"
                                :placeholder="`${estimasiNominal}`"
                                class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                                :class="{ 'border-rose-500': langgananForm.errors.nominal }"
                            />
                            <p class="mt-1 text-xs text-muted-foreground">Kosongkan = {{ formatRupiah(estimasiNominal) }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium" for="langganan-catatan">Catatan (opsional)</label>
                        <input
                            id="langganan-catatan"
                            v-model="langgananForm.catatan"
                            type="text"
                            placeholder="mis. transfer BCA 5 Jul"
                            class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                        />
                    </div>

                    <div
                        v-if="langgananToko.langganan_sampai"
                        class="flex items-center gap-2 rounded-lg bg-slate-50 p-3 text-xs text-muted-foreground dark:bg-zinc-800/50"
                    >
                        <CalendarClock class="h-4 w-4 shrink-0" />
                        Langganan saat ini sampai {{ formatDate(langgananToko.langganan_sampai) }} —
                        perpanjangan menumpuk dari tanggal itu bila masih aktif.
                    </div>

                    <div class="mt-2 flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-lg border border-sidebar-border/70 px-4 py-2 text-sm font-semibold transition hover:bg-slate-50 dark:border-sidebar-border dark:hover:bg-zinc-800"
                            @click="langgananToko = null"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="langgananForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 disabled:opacity-60"
                        >
                            <CreditCard class="h-4 w-4" />
                            Catat Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </BodyTeleport>
</template>
