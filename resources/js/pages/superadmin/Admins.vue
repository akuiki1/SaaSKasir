<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    AlertTriangle,
    ExternalLink,
    KeyRound,
    Mail,
    Search,
    Store,
    UserCog,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import BodyTeleport from '@/components/BodyTeleport.vue';
import Pagination from '@/components/Pagination.vue';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Kelola Admin', href: '/superadmin/admins' }],
    },
});

interface AdminItem {
    id: number;
    name: string;
    email: string;
    toko: { nama: string; slug: string | null; status: string } | null;
    created_at: string | null;
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
    admins: Paginator<AdminItem>;
    stats: { total_admin: number; toko_tanpa_admin: number };
    filters: { search: string; per_page: number };
}>();

const searchQuery = ref(props.filters.search ?? '');

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(searchQuery, (value) => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => reload({ search: value, page: 1 }), 350);
});

type QueryValue = string | number;

function reload(overrides: Record<string, QueryValue> = {}): void {
    const params: Record<string, QueryValue | undefined> = {
        search: searchQuery.value || undefined,
        per_page: props.filters.per_page,
        ...overrides,
    };

    const cleaned: Record<string, QueryValue> = {};
    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== '') {
            cleaned[key] = value;
        }
    });

    router.get('/superadmin/admins', cleaned, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const visiblePages = computed(() => {
    const pages: number[] = [];
    const total = props.admins.last_page;
    const current = props.admins.current_page;

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

// --- Reset password admin ---
const resetTarget = ref<AdminItem | null>(null);

const resetForm = useForm({
    password: '',
    password_confirmation: '',
});

function openReset(admin: AdminItem) {
    resetTarget.value = admin;
    resetForm.reset();
    resetForm.clearErrors();
}

function submitReset() {
    if (!resetTarget.value) {
        return;
    }

    resetForm.put(`/superadmin/admins/${resetTarget.value.id}/password`, {
        preserveScroll: true,
        onSuccess: () => {
            resetTarget.value = null;
        },
    });
}

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
    <Head title="Kelola Admin - Super Admin" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">Kelola Admin</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Akun pemilik toko di seluruh platform. Admin baru dibuat lewat
                "Daftarkan Toko Baru" di halaman Kelola Toko.
            </p>
        </div>

        <!-- Statistik ringkas -->
        <div class="grid gap-4 md:grid-cols-2">
            <div class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border">
                <div class="rounded-lg border border-sky-500/20 bg-sky-500/10 p-3 text-sky-600 dark:text-sky-400">
                    <UserCog class="h-6 w-6" />
                </div>
                <div>
                    <span class="text-xs font-medium text-muted-foreground">Total Admin</span>
                    <h3 class="mt-0.5 text-xl font-bold">{{ stats.total_admin }} Akun</h3>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border">
                <div
                    class="rounded-lg p-3"
                    :class="stats.toko_tanpa_admin > 0
                        ? 'border border-rose-500/20 bg-rose-500/10 text-rose-600 dark:text-rose-400'
                        : 'border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'"
                >
                    <AlertTriangle class="h-6 w-6" />
                </div>
                <div>
                    <span class="text-xs font-medium text-muted-foreground">Toko Tanpa Admin</span>
                    <h3 class="mt-0.5 text-xl font-bold">{{ stats.toko_tanpa_admin }} Toko</h3>
                </div>
            </div>
        </div>

        <!-- Tabel admin -->
        <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border">
            <div class="border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <div class="relative max-w-md">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama, email, atau nama toko..."
                        class="w-full rounded-lg border border-sidebar-border/70 bg-background py-2 pl-9 pr-4 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 bg-slate-50/50 dark:border-sidebar-border dark:bg-zinc-800/20">
                            <th class="px-6 py-4 font-semibold text-muted-foreground">Admin</th>
                            <th class="px-6 py-4 font-semibold text-muted-foreground">Email</th>
                            <th class="px-6 py-4 font-semibold text-muted-foreground">Toko</th>
                            <th class="px-6 py-4 font-semibold text-muted-foreground">Terdaftar</th>
                            <th class="px-6 py-4 text-right font-semibold text-muted-foreground">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        <tr v-if="props.admins.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                                <UserCog class="mx-auto mb-3 h-10 w-10 opacity-30" />
                                <p class="font-medium">
                                    {{ searchQuery ? 'Tidak ada admin yang sesuai pencarian.' : 'Belum ada akun admin.' }}
                                </p>
                            </td>
                        </tr>
                        <tr
                            v-for="admin in props.admins.data"
                            :key="admin.id"
                            class="transition-colors hover:bg-slate-50/50 dark:hover:bg-zinc-800/10"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-700 dark:bg-zinc-800 dark:text-slate-300">
                                        {{ admin.name.charAt(0) }}
                                    </div>
                                    <span class="font-semibold text-foreground">{{ admin.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-muted-foreground">
                                <div class="flex items-center gap-1.5">
                                    <Mail class="h-3.5 w-3.5" />
                                    {{ admin.email }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <template v-if="admin.toko">
                                    <div class="flex items-center gap-2">
                                        <Store class="h-3.5 w-3.5 text-muted-foreground" />
                                        <span class="font-medium">{{ admin.toko.nama }}</span>
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                            :class="admin.toko.status === 'aktif'
                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                : 'bg-rose-500/10 text-rose-600 dark:text-rose-400'"
                                        >
                                            {{ admin.toko.status }}
                                        </span>
                                    </div>
                                    <a
                                        v-if="admin.toko.slug"
                                        :href="`/toko/${admin.toko.slug}`"
                                        target="_blank"
                                        rel="noopener"
                                        class="mt-0.5 inline-flex items-center gap-1 text-xs text-muted-foreground transition hover:text-emerald-600"
                                    >
                                        /toko/{{ admin.toko.slug }}
                                        <ExternalLink class="h-3 w-3" />
                                    </a>
                                </template>
                                <span v-else class="text-xs text-rose-500">Tidak terhubung ke toko</span>
                            </td>
                            <td class="px-6 py-4 text-muted-foreground">{{ formatDate(admin.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-sidebar-border/70 px-3 py-1.5 text-xs font-semibold text-muted-foreground transition hover:bg-slate-50 hover:text-amber-600 dark:border-sidebar-border dark:hover:bg-zinc-800"
                                    @click="openReset(admin)"
                                >
                                    <KeyRound class="h-3.5 w-3.5" />
                                    Reset Password
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                :current-page="props.admins.current_page"
                :total-pages="props.admins.last_page"
                :total-items="props.admins.total"
                :start-index="props.admins.from ?? 0"
                :end-index="props.admins.to ?? 0"
                :per-page="props.admins.per_page"
                :visible-pages="visiblePages"
                @update:current-page="(page: number) => reload({ page })"
                @update:per-page="(value: number) => reload({ per_page: value, page: 1 })"
            />
        </div>
    </div>

    <!-- Modal: reset password -->
    <BodyTeleport>
        <div
            v-if="resetTarget"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @click.self="resetTarget = null"
        >
            <div class="w-full max-w-md rounded-2xl border border-sidebar-border/70 bg-card p-6 shadow-2xl dark:border-sidebar-border">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold">Reset Password</h2>
                        <p class="text-sm text-muted-foreground">{{ resetTarget.name }} · {{ resetTarget.email }}</p>
                    </div>
                    <button class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-slate-100 dark:hover:bg-zinc-800" aria-label="Tutup" @click="resetTarget = null">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <p class="mb-4 rounded-lg bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                    Gunakan hanya bila pemilik toko benar-benar tidak bisa masuk
                    (lupa password & reset email tidak memungkinkan). Sampaikan
                    password baru lewat kanal yang aman.
                </p>

                <form class="flex flex-col gap-4" @submit.prevent="submitReset">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium" for="reset-password">Password Baru</label>
                        <input
                            id="reset-password"
                            v-model="resetForm.password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                            :class="{ 'border-rose-500': resetForm.errors.password }"
                        />
                        <p v-if="resetForm.errors.password" class="mt-1 flex items-center gap-1 text-xs text-rose-600">
                            <AlertCircle class="h-3 w-3" />{{ resetForm.errors.password }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium" for="reset-password2">Konfirmasi Password Baru</label>
                        <input
                            id="reset-password2"
                            v-model="resetForm.password_confirmation"
                            type="password"
                            placeholder="Ulangi password baru"
                            class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none dark:border-sidebar-border"
                        />
                    </div>

                    <div class="mt-2 flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-lg border border-sidebar-border/70 px-4 py-2 text-sm font-semibold transition hover:bg-slate-50 dark:border-sidebar-border dark:hover:bg-zinc-800"
                            @click="resetTarget = null"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="resetForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-500 disabled:opacity-60"
                        >
                            <KeyRound class="h-4 w-4" />
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </BodyTeleport>
</template>
