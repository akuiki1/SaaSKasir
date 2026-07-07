<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    Download,
    PartyPopper,
    Upload,
} from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { importMethod, template } from '@/routes/admin/onboarding';
import { transaksi as kasirTransaksi } from '@/routes/kasir';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Onboarding', href: '/admin/onboarding' }],
    },
});

defineProps<{
    jumlah_produk: number;
}>();

interface HasilImport {
    berhasil: number;
    dilewati: string[];
}

const hasilImport = ref<HasilImport | null>(null);
let stopFlashListener: (() => void) | null = null;

onMounted(() => {
    stopFlashListener = router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;

        if (flash?.import_hasil) {
            hasilImport.value = flash.import_hasil as HasilImport;
        }
    });
});

onBeforeUnmount(() => {
    stopFlashListener?.();
});
</script>

<template>
    <Head title="Onboarding" />

    <div class="mx-auto max-w-2xl space-y-6 p-4 md:p-6">
        <div class="rounded-2xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border">
            <div class="flex items-start gap-3">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                >
                    <PartyPopper class="h-6 w-6" />
                </div>
                <div>
                    <h1 class="text-lg font-bold">Toko kamu siap!</h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Lengkapi daftar produk sekarang, atau langsung mulai
                        jualan dan tambahkan produk belakangan.
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border">
            <h2 class="font-bold">Impor produk dari Excel/CSV</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Kamu punya {{ jumlah_produk }} produk saat ini. Unduh
                template, isi daftar produkmu, lalu unggah di sini.
            </p>

            <a
                :href="template().url"
                class="mt-4 inline-flex items-center gap-2 rounded-lg border border-sidebar-border/70 px-4 py-2 text-sm font-semibold transition-colors hover:bg-accent dark:border-sidebar-border"
            >
                <Download class="h-4 w-4" />
                Unduh template CSV
            </a>

            <Form
                v-bind="importMethod.form()"
                v-slot="{ errors, processing }"
                class="mt-5 space-y-3"
            >
                <input
                    type="file"
                    name="file"
                    accept=".csv,.txt,.xlsx"
                    required
                    class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-foreground"
                />
                <InputError :message="errors.file" />

                <Button type="submit" :disabled="processing">
                    <Upload class="h-4 w-4" />
                    {{ processing ? 'Mengimpor...' : 'Impor produk' }}
                </Button>
            </Form>

            <div
                v-if="hasilImport"
                class="mt-5 rounded-xl border border-sidebar-border/70 p-4 text-sm dark:border-sidebar-border"
            >
                <p class="flex items-center gap-2 font-semibold text-emerald-600 dark:text-emerald-400">
                    <CheckCircle2 class="h-4 w-4" />
                    {{ hasilImport.berhasil }} produk berhasil diimpor.
                </p>
                <ul
                    v-if="hasilImport.dilewati.length > 0"
                    class="mt-2 list-inside list-disc space-y-1 text-muted-foreground"
                >
                    <li v-for="(alasan, i) in hasilImport.dilewati" :key="i">
                        {{ alasan }}
                    </li>
                </ul>
            </div>
        </div>

        <!-- Admin boleh akses POS kasir (RoleMiddleware: admin superset kasir),
             jadi "mulai jualan" benar-benar mendarat di halaman transaksi POS —
             bukan tabel Data Transaksi yang masih kosong untuk toko baru. -->
        <Link
            :href="kasirTransaksi().url"
            class="flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-4 text-center font-bold text-primary-foreground transition-transform hover:scale-[1.01] active:scale-[0.99]"
        >
            Lewati, mulai jualan
            <ArrowRight class="h-5 w-5" />
        </Link>
    </div>
</template>
