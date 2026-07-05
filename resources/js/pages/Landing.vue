<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    ArrowRightLeft,
    Check,
    CheckCircle2,
    Factory,
    Fuel,
    Menu,
    Moon,
    ScanLine,
    ShieldCheck,
    Sparkles,
    Store,
    Sun,
    WifiOff,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { formatRupiah } from '@/lib/format';

interface Tier {
    key: string;
    label: string;
    harga: number;
}

const props = defineProps<{
    tiers: Tier[];
    waKontak: string | null;
}>();

const { appearance, updateAppearance } = useAppearance();
const toggleTheme = () =>
    updateAppearance(appearance.value === 'dark' ? 'light' : 'dark');

const mobileMenuOpen = ref(false);

// Copy fitur per tier bersifat editorial (bukan config). Ditandai `soon` untuk
// fitur roadmap yang belum digulirkan penuh — jangan menjual yang belum ada.
const tierBullets: Record<
    string,
    { populer?: boolean; ringkas: string; fitur: { teks: string; soon?: boolean }[] }
> = {
    gratis: {
        ringkas: 'Untuk mulai merapikan warung tanpa biaya.',
        fitur: [
            { teks: 'Kasir & transaksi tanpa batas' },
            { teks: 'Kelola produk, stok & kartu stok' },
            { teks: 'Jual satuan, curah & jasa transfer/tarik tunai' },
            { teks: 'Toko online + pesanan via WhatsApp' },
        ],
    },
    warung: {
        populer: true,
        ringkas: 'Untuk warung yang mau tahu untung aslinya.',
        fitur: [
            { teks: 'Semua fitur Gratis' },
            { teks: 'Laporan keuangan & laba–rugi' },
            { teks: 'Produksi & hitung HPP otomatis' },
            { teks: 'Promo & harga khusus reseller' },
            { teks: 'Mode offline (tetap jualan saat internet mati)' },
        ],
    },
    bisnis: {
        ringkas: 'Untuk yang punya karyawan & mau naik kelas.',
        fitur: [
            { teks: 'Semua fitur Warung' },
            { teks: 'Multi-pengguna & hak akses' },
            { teks: 'Prioritas dukungan' },
            { teks: 'Multi-cabang', soon: true },
            { teks: 'Ekspor pembukuan siap ajuan KUR', soon: true },
        ],
    },
};

const pains = [
    'Tiap malam rekap manual, tetap sering nggak balance.',
    'Stok raib entah ke mana, ketahuan pas sudah rugi.',
    'Fee transfer & tarik tunai campur omzet — bingung untung aslinya.',
    'Mau ajukan KUR, tapi pembukuan berantakan.',
];

const features = [
    {
        icon: Fuel,
        judul: 'Jual curah by-rupiah',
        teks: 'Bensin, minyak, bawang — ketik nominal rupiahnya, kuantitas & potong stok dihitung otomatis sampai pecahan.',
    },
    {
        icon: ArrowRightLeft,
        judul: 'Agen transfer & tarik tunai',
        teks: 'Fee jadi omzet, uang titipan pelanggan tidak dihitung untung. Pembukuan jasa keuangan akhirnya benar.',
    },
    {
        icon: Factory,
        judul: 'Produksi & HPP',
        teks: 'Frozen food, catering, kue — tahu modal dan untung tiap batch, bukan kira-kira.',
    },
    {
        icon: Store,
        judul: 'Toko online otomatis',
        teks: 'Pelanggan pesan lewat link, notifikasi masuk WhatsApp. Etalase terisi dari produk yang kamu jual.',
    },
    {
        icon: WifiOff,
        judul: 'Tetap jalan saat internet mati',
        teks: 'Transaksi tersimpan di perangkat & cetak struk tetap bisa; sinkron otomatis begitu online kembali.',
    },
    {
        icon: ScanLine,
        judul: 'Scanner & printer thermal',
        teks: 'Scan barcode dan cetak struk 58mm langsung dari HP — tanpa perangkat kasir mahal.',
    },
];

const hargaTermurahBerbayar = computed(() => {
    const berbayar = props.tiers.filter((t) => t.harga > 0);

    return berbayar.length > 0
        ? Math.min(...berbayar.map((t) => t.harga))
        : 0;
});

const waHref = computed(() =>
    props.waKontak
        ? `https://wa.me/${props.waKontak}?text=${encodeURIComponent('Halo, saya mau tanya soal SiKasir untuk warung saya.')}`
        : null,
);
</script>

<template>
    <Head title="SiKasir — Kasir pintar untuk warung serba-ada & agen transfer">
        <meta
            name="description"
            content="SiKasir bikin warungmu—termasuk transfer, tarik tunai, bensin eceran, sampai produksi rumahan—terkelola rapi dalam satu aplikasi. Tetap jualan walau internet mati. Coba gratis."
        />
    </Head>

    <div class="min-h-screen bg-background text-foreground">
        <!-- ============ Nav ============ -->
        <header
            class="sticky top-0 z-50 border-b border-sidebar-border/60 bg-background/80 backdrop-blur-md"
        >
            <div
                class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6"
            >
                <a href="#top" class="flex items-center gap-2">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm"
                    >
                        <Store class="h-5 w-5" />
                    </span>
                    <span class="text-lg font-extrabold tracking-tight"
                        >SiKasir</span
                    >
                </a>

                <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
                    <a href="#masalah" class="text-muted-foreground transition hover:text-foreground">Masalah</a>
                    <a href="#fitur" class="text-muted-foreground transition hover:text-foreground">Fitur</a>
                    <a href="#harga" class="text-muted-foreground transition hover:text-foreground">Harga</a>
                </nav>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        :aria-label="appearance === 'dark' ? 'Mode terang' : 'Mode gelap'"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-sidebar-border/70 text-muted-foreground transition hover:text-foreground"
                        @click="toggleTheme"
                    >
                        <Sun v-if="appearance === 'dark'" class="h-4 w-4" />
                        <Moon v-else class="h-4 w-4" />
                    </button>
                    <Link
                        href="/login"
                        class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-muted-foreground transition hover:text-foreground sm:inline-flex"
                        >Masuk</Link
                    >
                    <Link
                        href="/register"
                        class="hidden items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500 sm:inline-flex"
                    >
                        Daftar Gratis
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                    <button
                        type="button"
                        aria-label="Menu"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-sidebar-border/70 md:hidden"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Menu mobile -->
            <div
                v-if="mobileMenuOpen"
                class="border-t border-sidebar-border/60 bg-background px-4 py-3 md:hidden"
            >
                <div class="flex flex-col gap-1 text-sm font-medium">
                    <a href="#masalah" class="rounded-lg px-3 py-2 hover:bg-muted" @click="mobileMenuOpen = false">Masalah</a>
                    <a href="#fitur" class="rounded-lg px-3 py-2 hover:bg-muted" @click="mobileMenuOpen = false">Fitur</a>
                    <a href="#harga" class="rounded-lg px-3 py-2 hover:bg-muted" @click="mobileMenuOpen = false">Harga</a>
                    <Link href="/login" class="rounded-lg px-3 py-2 hover:bg-muted">Masuk</Link>
                    <Link
                        href="/register"
                        class="mt-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2.5 font-bold text-white"
                    >
                        Daftar Gratis
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </header>

        <main id="top">
            <!-- ============ Hero ============ -->
            <section class="relative overflow-hidden">
                <div
                    class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-emerald-500/10 via-transparent to-transparent"
                ></div>
                <div
                    class="mx-auto max-w-6xl px-4 pt-16 pb-12 text-center sm:px-6 sm:pt-24 sm:pb-20"
                >
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400"
                    >
                        <Sparkles class="h-3.5 w-3.5" />
                        Kasir untuk warung serba-ada & agen transfer
                    </span>

                    <h1
                        class="mx-auto mt-6 max-w-3xl text-4xl font-extrabold tracking-tight sm:text-5xl md:text-6xl"
                    >
                        Jualan beres, laporan otomatis,
                        <span class="text-emerald-600 dark:text-emerald-500">
                            stok tak bocor.</span
                        >
                    </h1>

                    <p
                        class="mx-auto mt-5 max-w-2xl text-base text-muted-foreground sm:text-lg"
                    >
                        SiKasir merapikan warungmu—dari transfer, tarik tunai,
                        bensin eceran, sampai produksi rumahan—dalam satu
                        aplikasi. Tetap jualan walau internet mati.
                    </p>

                    <div
                        class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
                    >
                        <Link
                            href="/register"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3.5 text-base font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-500 sm:w-auto"
                        >
                            Coba Gratis Sekarang
                            <ArrowRight class="h-5 w-5" />
                        </Link>
                        <a
                            href="#harga"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-sidebar-border/70 px-6 py-3.5 text-base font-bold transition hover:bg-muted sm:w-auto"
                        >
                            Lihat harga
                        </a>
                    </div>

                    <p
                        class="mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-1 text-xs font-medium text-muted-foreground"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            <ShieldCheck class="h-4 w-4 text-emerald-600" />
                            Garansi 30 hari uang kembali
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <CheckCircle2 class="h-4 w-4 text-emerald-600" />
                            Tanpa kartu kredit
                        </span>
                    </p>
                </div>
            </section>

            <!-- ============ Masalah ============ -->
            <section id="masalah" class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">
                        Kalau ini kamu banget…
                    </h2>
                    <p class="mt-3 text-muted-foreground">
                        Kebocoran kecil yang menggerus untung tiap hari — dan
                        biasanya baru ketahuan setelah terlambat.
                    </p>
                </div>

                <div class="mx-auto mt-10 grid max-w-3xl gap-3 sm:grid-cols-2">
                    <div
                        v-for="(pain, i) in pains"
                        :key="i"
                        class="flex items-start gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4"
                    >
                        <span
                            class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400"
                        >
                            <X class="h-3.5 w-3.5" />
                        </span>
                        <p class="text-sm">{{ pain }}</p>
                    </div>
                </div>
            </section>

            <!-- ============ Fitur ============ -->
            <section
                id="fitur"
                class="border-y border-sidebar-border/60 bg-muted/30 py-16"
            >
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2
                            class="text-2xl font-extrabold tracking-tight sm:text-3xl"
                        >
                            Dibuat untuk cara jualanmu yang sebenarnya
                        </h2>
                        <p class="mt-3 text-muted-foreground">
                            Bukan sekadar aplikasi kasir generik — SiKasir paham
                            warung yang menjual apa saja sekaligus jadi agen
                            keuangan.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="feature in features"
                            :key="feature.judul"
                            class="rounded-2xl border border-sidebar-border/70 bg-card p-6 transition hover:border-emerald-500/40 hover:shadow-md"
                        >
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-600/10 text-emerald-600 dark:text-emerald-500"
                            >
                                <component :is="feature.icon" class="h-6 w-6" />
                            </span>
                            <h3 class="mt-4 text-base font-bold">
                                {{ feature.judul }}
                            </h3>
                            <p class="mt-2 text-sm text-muted-foreground">
                                {{ feature.teks }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ Harga ============ -->
            <section id="harga" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">
                        Harga jujur, mulai dari gratis
                    </h2>
                    <p class="mt-3 text-muted-foreground">
                        Mulai gratis selamanya. Naik tier hanya saat kamu butuh
                        laporan untung-rugi dan alat yang lebih lengkap.
                    </p>
                </div>

                <div class="mt-12 grid items-start gap-6 lg:grid-cols-3">
                    <div
                        v-for="tier in tiers"
                        :key="tier.key"
                        class="relative flex flex-col rounded-2xl border bg-card p-6"
                        :class="
                            tierBullets[tier.key]?.populer
                                ? 'border-emerald-500 shadow-lg shadow-emerald-600/10 lg:-mt-2 lg:mb-2'
                                : 'border-sidebar-border/70'
                        "
                    >
                        <span
                            v-if="tierBullets[tier.key]?.populer"
                            class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white"
                        >
                            Paling populer
                        </span>

                        <h3 class="text-lg font-extrabold">{{ tier.label }}</h3>
                        <p class="mt-1 min-h-[2.5rem] text-sm text-muted-foreground">
                            {{ tierBullets[tier.key]?.ringkas }}
                        </p>

                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-3xl font-extrabold tracking-tight">
                                {{ tier.harga === 0 ? 'Gratis' : formatRupiah(tier.harga) }}
                            </span>
                            <span
                                v-if="tier.harga > 0"
                                class="text-sm font-medium text-muted-foreground"
                                >/bulan</span
                            >
                        </div>

                        <Link
                            href="/register"
                            class="mt-6 inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-bold transition"
                            :class="
                                tierBullets[tier.key]?.populer
                                    ? 'bg-emerald-600 text-white hover:bg-emerald-500'
                                    : 'border border-sidebar-border/70 hover:bg-muted'
                            "
                        >
                            {{ tier.harga === 0 ? 'Mulai gratis' : 'Pilih ' + tier.label }}
                            <ArrowRight class="h-4 w-4" />
                        </Link>

                        <ul class="mt-6 space-y-3 text-sm">
                            <li
                                v-for="(f, i) in tierBullets[tier.key]?.fitur"
                                :key="i"
                                class="flex items-start gap-2.5"
                            >
                                <Check
                                    class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"
                                />
                                <span :class="f.soon ? 'text-muted-foreground' : ''">
                                    {{ f.teks }}
                                    <span
                                        v-if="f.soon"
                                        class="ml-1 rounded bg-muted px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                        >Segera</span
                                    >
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <p class="mt-6 text-center text-xs text-muted-foreground">
                    Butuh dipasangkan langsung + perangkat? Tersedia paket
                    pemasangan lokal mulai
                    {{ hargaTermurahBerbayar > 0 ? formatRupiah(hargaTermurahBerbayar) + '/bln' : '' }}.
                    Fitur bertanda “Segera” sedang digulirkan bertahap.
                </p>
            </section>

            <!-- ============ Studi kasus ============ -->
            <section class="border-y border-sidebar-border/60 bg-muted/30 py-16">
                <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
                    <Wallet
                        class="mx-auto h-8 w-8 text-emerald-600 dark:text-emerald-500"
                    />
                    <blockquote
                        class="mt-5 text-xl font-semibold tracking-tight sm:text-2xl"
                    >
                        “Sekarang saya tahu untung asli tiap hari, dan stok tidak
                        lagi hilang diam-diam. Rekap malam yang dulu sejam,
                        sekarang selesai sendiri.”
                    </blockquote>
                    <p class="mt-4 text-sm font-medium text-muted-foreground">
                        — Pemilik warung serba-ada, pengguna awal SiKasir
                    </p>
                </div>
            </section>

            <!-- ============ CTA akhir ============ -->
            <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div
                    class="overflow-hidden rounded-3xl bg-emerald-600 px-6 py-14 text-center text-white sm:px-12"
                >
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-4xl">
                        Siap bikin warungmu naik kelas?
                    </h2>
                    <p class="mx-auto mt-4 max-w-xl text-emerald-50">
                        Daftar gratis dalam semenit. Kalau dalam 30 hari tidak
                        terbantu, uangmu kembali.
                    </p>
                    <div
                        class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
                    >
                        <Link
                            href="/register"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-6 py-3.5 text-base font-bold text-emerald-700 shadow-lg transition hover:bg-emerald-50 sm:w-auto"
                        >
                            Daftar Gratis
                            <ArrowRight class="h-5 w-5" />
                        </Link>
                        <a
                            v-if="waHref"
                            :href="waHref"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/40 px-6 py-3.5 text-base font-bold text-white transition hover:bg-white/10 sm:w-auto"
                        >
                            Tanya lewat WhatsApp
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <!-- ============ Footer ============ -->
        <footer class="border-t border-sidebar-border/60">
            <div
                class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm sm:flex-row sm:px-6"
            >
                <div class="flex items-center gap-2">
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-600 text-white"
                    >
                        <Store class="h-4 w-4" />
                    </span>
                    <span class="font-bold">SiKasir</span>
                    <span class="text-muted-foreground"
                        >— kasir yang bikin warung beres.</span
                    >
                </div>
                <div class="flex items-center gap-5 text-muted-foreground">
                    <Link href="/login" class="transition hover:text-foreground">Masuk</Link>
                    <Link href="/register" class="transition hover:text-foreground">Daftar</Link>
                    <a
                        v-if="waHref"
                        :href="waHref"
                        target="_blank"
                        rel="noopener"
                        class="transition hover:text-foreground"
                        >WhatsApp</a
                    >
                </div>
            </div>
        </footer>
    </div>
</template>
