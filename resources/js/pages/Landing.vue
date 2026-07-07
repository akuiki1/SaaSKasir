<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    ArrowRightLeft,
    BarChart3,
    Check,
    CheckCircle2,
    ChevronDown,
    Factory,
    Fuel,
    Menu,
    MonitorSmartphone,
    Moon,
    Package,
    Pill,
    ScanLine,
    ShieldCheck,
    ShoppingBasket,
    ShoppingCart,
    Sparkles,
    Store,
    Sun,
    WifiOff,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
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

// Nav berubah solid+blur setelah discroll sedikit, dan gambar hero bergeser
// halus (parallax) seiring scroll — satu listener untuk keduanya.
const navScrolled = ref(false);
const heroParallaxY = ref(0);
const heroRef = ref<HTMLElement | null>(null);

function onScroll() {
    navScrolled.value = window.scrollY > 8;

    if (heroRef.value) {
        const rect = heroRef.value.getBoundingClientRect();
        const progress = Math.min(
            1,
            Math.max(0, -rect.top / (rect.height || 1)),
        );
        heroParallaxY.value = progress * 30;
    }
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

// FAQ: satu item terbuka dalam satu waktu, item pertama terbuka secara default.
const openFaqIndex = ref(0);
function toggleFaq(i: number) {
    openFaqIndex.value = openFaqIndex.value === i ? -1 : i;
}

// Copy fitur per tier bersifat editorial (bukan config). Ditandai `soon` untuk
// fitur roadmap yang belum digulirkan penuh — jangan menjual yang belum ada.
const tierBullets: Record<
    string,
    {
        populer?: boolean;
        ringkas: string;
        fitur: { teks: string; soon?: boolean }[];
    }
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

// Strip 4 keunggulan di bawah hero (mengikuti desain mockup).
const heroStrip = [
    {
        icon: ShoppingCart,
        judul: 'Transaksi Cepat',
        teks: 'Proses penjualan lebih cepat dan praktis, kurangi antrean di kasir.',
    },
    {
        icon: Package,
        judul: 'Kelola Stok Mudah',
        teks: 'Pantau stok barang secara real-time dan dapatkan notifikasi stok rendah.',
    },
    {
        icon: BarChart3,
        judul: 'Laporan Lengkap',
        teks: 'Laporan penjualan harian, mingguan, bulanan lengkap dan mudah dipahami.',
    },
    {
        icon: MonitorSmartphone,
        judul: 'Akses di Mana Saja',
        teks: 'Bisa diakses dari berbagai perangkat, kapan saja dan di mana saja.',
    },
];

// Bukan nama pelanggan spesifik (belum ada logo klien nyata untuk dipajang) —
// kategori usaha yang memang jadi target produk, biar jujur tapi tetap
// meyakinkan.
const trustCategories = [
    { icon: Store, label: 'Warung Sembako' },
    { icon: ArrowRightLeft, label: 'Agen Transfer & Tarik Tunai' },
    { icon: Fuel, label: 'Bensin Eceran' },
    { icon: Factory, label: 'Produksi Rumahan & Katering' },
    { icon: ShoppingBasket, label: 'Toko Kelontong' },
    { icon: Pill, label: 'Apotek' },
];

const caraKerja = [
    {
        judul: 'Daftar toko dalam semenit',
        teks: 'Isi nama toko, nama kamu, email, dan nomor WhatsApp. Toko dan akun admin langsung aktif — tanpa kartu kredit, tanpa menunggu.',
    },
    {
        judul: 'Impor produk, atau lewati dulu',
        teks: 'Unggah daftar produk dari file CSV/Excel sekali jalan, atau lewati langkah ini dan tambah produk belakangan — kamu yang atur urutannya.',
    },
    {
        judul: 'Mulai transaksi di kasir',
        teks: 'Buka halaman kasir, scan atau cari produk, dan mulai catat penjualan hari itu juga — termasuk transfer, tarik tunai, atau jual curah.',
    },
    {
        judul: 'Pantau laporan untung-rugi',
        teks: 'Lihat rekap penjualan, stok, dan laba-rugi kapan saja — tanpa rekap manual tiap malam.',
    },
];

const faqItems = [
    {
        q: 'Apakah SiKasir gratis?',
        a: 'Ya — tier Gratis berlaku selamanya, tanpa kartu kredit dan tanpa batas waktu. Kamu bisa upgrade ke tier berbayar kapan pun butuh laporan laba-rugi atau fitur yang lebih lengkap, tapi tidak wajib.',
    },
    {
        q: 'Apakah SiKasir bisa dipakai tanpa internet?',
        a: 'Bisa. Transaksi tetap tersimpan di perangkat dan struk tetap bisa dicetak saat internet mati, lalu otomatis tersinkron begitu koneksi kembali.',
    },
    {
        q: 'Bagaimana cara upgrade atau ganti tier?',
        a: 'Dari dashboard toko kamu bisa lihat perbandingan tier dan mengajukan upgrade kapan saja — tidak ada kontrak jangka panjang atau biaya pembatalan.',
    },
    {
        q: 'Apakah data toko saya aman?',
        a: 'Data tersimpan dengan praktik pencadangan standar dan hanya bisa diakses lewat akun toko kamu. Kami terus meningkatkan keamanan seiring SiKasir berkembang.',
    },
    {
        q: 'Apakah SiKasir cocok untuk usaha selain warung, misalnya jasa transfer atau produksi rumahan?',
        a: 'Cocok. SiKasir dibangun untuk usaha yang jualan lebih dari satu hal sekaligus — termasuk agen transfer/tarik tunai, jual curah seperti bensin eceran, dan produksi rumahan dengan hitung HPP otomatis.',
    },
];

const hargaTermurahBerbayar = computed(() => {
    const berbayar = props.tiers.filter((t) => t.harga > 0);

    return berbayar.length > 0 ? Math.min(...berbayar.map((t) => t.harga)) : 0;
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
            class="sticky top-0 z-50 border-b transition-colors duration-300"
            :class="
                navScrolled
                    ? 'border-sidebar-border/60 bg-background/80 backdrop-blur-md'
                    : 'border-transparent bg-transparent'
            "
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

                <nav
                    class="hidden items-center gap-6 text-sm font-medium lg:flex"
                >
                    <a
                        href="#top"
                        class="text-muted-foreground transition hover:text-foreground"
                        >Beranda</a
                    >
                    <a
                        href="#masalah"
                        class="text-muted-foreground transition hover:text-foreground"
                        >Masalah</a
                    >
                    <a
                        href="#fitur"
                        class="text-muted-foreground transition hover:text-foreground"
                        >Fitur</a
                    >
                    <a
                        href="#cara-kerja"
                        class="text-muted-foreground transition hover:text-foreground"
                        >Cara Kerja</a
                    >
                    <a
                        href="#harga"
                        class="text-muted-foreground transition hover:text-foreground"
                        >Harga</a
                    >
                    <a
                        href="#faq"
                        class="text-muted-foreground transition hover:text-foreground"
                        >Bantuan</a
                    >
                </nav>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        :aria-label="
                            appearance === 'dark' ? 'Mode terang' : 'Mode gelap'
                        "
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
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-sidebar-border/70 lg:hidden"
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
                class="border-t border-sidebar-border/60 bg-background px-4 py-3 lg:hidden"
            >
                <div class="flex flex-col gap-1 text-sm font-medium">
                    <a
                        href="#top"
                        class="rounded-lg px-3 py-2 hover:bg-muted"
                        @click="mobileMenuOpen = false"
                        >Beranda</a
                    >
                    <a
                        href="#masalah"
                        class="rounded-lg px-3 py-2 hover:bg-muted"
                        @click="mobileMenuOpen = false"
                        >Masalah</a
                    >
                    <a
                        href="#fitur"
                        class="rounded-lg px-3 py-2 hover:bg-muted"
                        @click="mobileMenuOpen = false"
                        >Fitur</a
                    >
                    <a
                        href="#cara-kerja"
                        class="rounded-lg px-3 py-2 hover:bg-muted"
                        @click="mobileMenuOpen = false"
                        >Cara Kerja</a
                    >
                    <a
                        href="#harga"
                        class="rounded-lg px-3 py-2 hover:bg-muted"
                        @click="mobileMenuOpen = false"
                        >Harga</a
                    >
                    <a
                        href="#faq"
                        class="rounded-lg px-3 py-2 hover:bg-muted"
                        @click="mobileMenuOpen = false"
                        >Bantuan</a
                    >
                    <Link
                        href="/login"
                        class="rounded-lg px-3 py-2 hover:bg-muted"
                        >Masuk</Link
                    >
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
            <section ref="heroRef" class="relative overflow-hidden">
                <div
                    class="mx-auto max-w-6xl px-4 pt-16 pb-12 sm:px-6 sm:pt-24 sm:pb-20"
                >
                    <div
                        class="grid items-center gap-12 lg:grid-cols-2 lg:gap-10"
                    >
                        <div class="text-center lg:text-left">
                            <span
                                class="animate-fade-in-up animate-delay-1 inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400"
                            >
                                <Sparkles class="h-3.5 w-3.5" />
                                Kasir untuk warung serba-ada & agen transfer
                            </span>

                            <h1
                                class="mx-auto mt-6 max-w-3xl text-4xl font-extrabold tracking-tight sm:text-5xl md:text-6xl lg:mx-0"
                            >
                                <span
                                    class="animate-fade-in-up animate-delay-2 block"
                                    >Jualan beres, laporan otomatis,</span
                                >
                                <span
                                    class="animate-fade-in-up animate-delay-3 block"
                                >
                                    <span
                                        class="text-emerald-600 dark:text-emerald-500"
                                        >stok tak bocor.</span
                                    >
                                </span>
                            </h1>

                            <p
                                class="animate-fade-in-up animate-delay-4 mx-auto mt-5 max-w-2xl text-base text-muted-foreground sm:text-lg lg:mx-0"
                            >
                                SiKasir merapikan warungmu—dari transfer, tarik
                                tunai, bensin eceran, sampai produksi
                                rumahan—dalam satu aplikasi. Tetap jualan walau
                                internet mati.
                            </p>

                            <div
                                class="animate-fade-in-up animate-delay-5 mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row lg:justify-start"
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
                                class="animate-fade-in-up animate-delay-6 mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-1 text-xs font-medium text-muted-foreground lg:justify-start"
                            >
                                <span class="inline-flex items-center gap-1.5">
                                    <ShieldCheck
                                        class="h-4 w-4 text-emerald-600"
                                    />
                                    Garansi 30 hari uang kembali
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <CheckCircle2
                                        class="h-4 w-4 text-emerald-600"
                                    />
                                    Tanpa kartu kredit
                                </span>
                            </p>
                        </div>

                        <div
                            class="relative flex justify-center lg:justify-end"
                        >
                            <div
                                class="relative w-full max-w-[300px] sm:max-w-[400px] lg:max-w-[580px]"
                            >
                                <!-- Latar hijau: blob besar radial di belakang perangkat -->
                                <div
                                    class="animate-blob-1 pointer-events-none absolute -top-[46%] left-[46%] z-0 aspect-square w-[120%] rounded-full bg-[radial-gradient(circle_at_28%_45%,#E9F7EF_0%,#DEF1E6_45%,#D0EADC_78%,#C6E5D3_100%)] dark:bg-[radial-gradient(circle_at_28%_45%,rgba(16,185,129,0.20)_0%,rgba(6,78,59,0.16)_55%,transparent_100%)]"
                                ></div>
                                <!-- Blob kecil solid, kanan-bawah -->
                                <div
                                    class="animate-blob-2 pointer-events-none absolute top-[44%] left-[80%] z-0 aspect-square w-[54%] rounded-full bg-[#B7E0C8] dark:bg-emerald-800/40"
                                ></div>
                                <!-- Grid titik hijau -->
                                <div
                                    class="animate-dots-pulse pointer-events-none absolute top-[6%] left-[84%] z-0 h-[60px] w-[76px] [background-image:radial-gradient(#3FA268_1.6px,transparent_1.6px)] [background-size:15px_15px] dark:[background-image:radial-gradient(#34d399_1.6px,transparent_1.6px)]"
                                ></div>

                                <!-- Perangkat mengambang + parallax -->
                                <div class="animate-float relative z-10">
                                    <div
                                        :style="{
                                            transform: `translateY(${heroParallaxY}px)`,
                                        }"
                                    >
                                        <img
                                            src="/images/hero-devices-cropped.png"
                                            alt="Tampilan aplikasi SiKasir di tablet, printer struk, dan HP"
                                            width="902"
                                            height="658"
                                            class="w-full drop-shadow-[0_46px_70px_rgba(15,40,28,0.28)] dark:drop-shadow-[0_46px_70px_rgba(0,0,0,0.5)]"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Strip 4 keunggulan -->
                    <div
                        class="animate-fade-in-up animate-delay-7 mt-14 sm:mt-20"
                    >
                        <div
                            class="grid gap-x-8 gap-y-7 rounded-2xl border border-sidebar-border/70 bg-card p-8 shadow-[0_30px_60px_-34px_rgba(15,40,28,0.16)] sm:grid-cols-2 lg:grid-cols-4"
                        >
                            <div
                                v-for="item in heroStrip"
                                :key="item.judul"
                                class="flex items-start gap-4"
                            >
                                <span
                                    class="flex h-[54px] w-[54px] shrink-0 items-center justify-center rounded-full bg-emerald-600/10 text-emerald-600 dark:text-emerald-500"
                                >
                                    <component
                                        :is="item.icon"
                                        class="h-6 w-6"
                                    />
                                </span>
                                <div>
                                    <div class="text-base font-extrabold">
                                        {{ item.judul }}
                                    </div>
                                    <p
                                        class="mt-1.5 text-sm leading-relaxed text-muted-foreground"
                                    >
                                        {{ item.teks }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ Trust bar ============ -->
            <section
                class="border-y border-sidebar-border/60 bg-muted/30 py-10"
            >
                <p
                    class="text-center text-sm font-semibold text-muted-foreground"
                >
                    Dipakai berbagai jenis usaha kecil di Indonesia
                </p>
                <div
                    class="relative mt-8 w-full overflow-hidden [mask-image:linear-gradient(90deg,transparent,black_10%,black_90%,transparent)]"
                >
                    <div
                        class="animate-marquee flex w-max gap-16 hover:[animation-play-state:paused]"
                    >
                        <div
                            v-for="(cat, i) in [
                                ...trustCategories,
                                ...trustCategories,
                            ]"
                            :key="i"
                            class="flex shrink-0 items-center gap-2.5 opacity-70"
                            :aria-hidden="i >= trustCategories.length"
                        >
                            <component
                                :is="cat.icon"
                                class="h-5 w-5 text-muted-foreground"
                            />
                            <span
                                class="text-sm leading-tight font-bold text-muted-foreground"
                                >{{ cat.label }}</span
                            >
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ Masalah ============ -->
            <section id="masalah" class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <h2
                        class="text-2xl font-extrabold tracking-tight sm:text-3xl"
                    >
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
                            class="rounded-2xl border border-sidebar-border/70 bg-card p-6 transition hover:-translate-y-1 hover:border-emerald-500/40 hover:shadow-md"
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

            <!-- ============ Cara Kerja ============ -->
            <section
                id="cara-kerja"
                class="mx-auto max-w-6xl px-4 py-16 sm:px-6"
            >
                <div class="mx-auto max-w-2xl text-center">
                    <h2
                        class="text-2xl font-extrabold tracking-tight sm:text-3xl"
                    >
                        Mulai Jualan dalam 4 Langkah
                    </h2>
                    <p class="mt-3 text-muted-foreground">
                        Tanpa instalasi rumit — dari daftar sampai transaksi
                        pertama, semua bisa selesai hari ini.
                    </p>
                </div>

                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="(step, i) in caraKerja"
                        :key="i"
                        class="relative"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-lg font-extrabold text-white"
                        >
                            {{ String(i + 1).padStart(2, '0') }}
                        </div>
                        <h3 class="mt-5 text-base font-bold">
                            {{ step.judul }}
                        </h3>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ step.teks }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ============ Harga ============ -->
            <section id="harga" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <h2
                        class="text-2xl font-extrabold tracking-tight sm:text-3xl"
                    >
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
                        class="relative flex flex-col rounded-2xl border p-6 transition"
                        :class="
                            tierBullets[tier.key]?.populer
                                ? 'border-emerald-900 bg-emerald-950 text-white shadow-xl shadow-emerald-950/20 lg:-mt-2 lg:mb-2 dark:border-emerald-800'
                                : 'border-sidebar-border/70 bg-card'
                        "
                    >
                        <span
                            v-if="tierBullets[tier.key]?.populer"
                            class="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-lg shadow-emerald-600/30"
                        >
                            Paling populer
                        </span>

                        <h3
                            class="text-lg font-extrabold"
                            :class="
                                tierBullets[tier.key]?.populer
                                    ? 'text-white'
                                    : ''
                            "
                        >
                            {{ tier.label }}
                        </h3>
                        <p
                            class="mt-1 min-h-[2.5rem] text-sm"
                            :class="
                                tierBullets[tier.key]?.populer
                                    ? 'text-emerald-100/80'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ tierBullets[tier.key]?.ringkas }}
                        </p>

                        <div class="mt-4 flex items-baseline gap-1">
                            <span
                                class="text-3xl font-extrabold tracking-tight"
                                :class="
                                    tierBullets[tier.key]?.populer
                                        ? 'text-white'
                                        : ''
                                "
                            >
                                {{
                                    tier.harga === 0
                                        ? 'Gratis'
                                        : formatRupiah(tier.harga)
                                }}
                            </span>
                            <span
                                v-if="tier.harga > 0"
                                class="text-sm font-medium"
                                :class="
                                    tierBullets[tier.key]?.populer
                                        ? 'text-emerald-100/70'
                                        : 'text-muted-foreground'
                                "
                                >/bulan</span
                            >
                        </div>

                        <Link
                            href="/register"
                            class="mt-6 inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-bold transition"
                            :class="
                                tierBullets[tier.key]?.populer
                                    ? 'bg-emerald-500 text-white hover:bg-emerald-400'
                                    : 'border border-sidebar-border/70 hover:bg-muted'
                            "
                        >
                            {{
                                tier.harga === 0
                                    ? 'Mulai gratis'
                                    : 'Pilih ' + tier.label
                            }}
                            <ArrowRight class="h-4 w-4" />
                        </Link>

                        <ul class="mt-6 space-y-3 text-sm">
                            <li
                                v-for="(f, i) in tierBullets[tier.key]?.fitur"
                                :key="i"
                                class="flex items-start gap-2.5"
                            >
                                <Check
                                    class="mt-0.5 h-4 w-4 shrink-0"
                                    :class="
                                        tierBullets[tier.key]?.populer
                                            ? 'text-emerald-400'
                                            : 'text-emerald-600'
                                    "
                                />
                                <span
                                    :class="
                                        tierBullets[tier.key]?.populer
                                            ? f.soon
                                                ? 'text-emerald-100/60'
                                                : 'text-emerald-50'
                                            : f.soon
                                              ? 'text-muted-foreground'
                                              : ''
                                    "
                                >
                                    {{ f.teks }}
                                    <span
                                        v-if="f.soon"
                                        class="ml-1 rounded px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase"
                                        :class="
                                            tierBullets[tier.key]?.populer
                                                ? 'bg-white/10 text-emerald-100/70'
                                                : 'bg-muted text-muted-foreground'
                                        "
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
                    {{
                        hargaTermurahBerbayar > 0
                            ? formatRupiah(hargaTermurahBerbayar) + '/bln'
                            : ''
                    }}. Fitur bertanda “Segera” sedang digulirkan bertahap.
                </p>
            </section>

            <!-- ============ Studi kasus ============ -->
            <section
                class="border-y border-sidebar-border/60 bg-muted/30 py-16"
            >
                <div class="mx-auto max-w-2xl px-4 sm:px-6">
                    <div
                        class="rounded-2xl border border-sidebar-border/70 bg-card p-8 text-center shadow-sm"
                    >
                        <Wallet
                            class="mx-auto h-8 w-8 text-emerald-600 dark:text-emerald-500"
                        />
                        <blockquote
                            class="mt-5 text-xl font-semibold tracking-tight sm:text-2xl"
                        >
                            “Sekarang saya tahu untung asli tiap hari, dan stok
                            tidak lagi hilang diam-diam. Rekap malam yang dulu
                            sejam, sekarang selesai sendiri.”
                        </blockquote>
                        <p
                            class="mt-4 text-sm font-medium text-muted-foreground"
                        >
                            — Pemilik warung serba-ada, pengguna awal SiKasir
                        </p>
                    </div>
                </div>
            </section>

            <!-- ============ FAQ ============ -->
            <section id="faq" class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
                <div class="text-center">
                    <h2
                        class="text-2xl font-extrabold tracking-tight sm:text-3xl"
                    >
                        Pertanyaan yang Sering Diajukan
                    </h2>
                </div>

                <div class="mt-10">
                    <div
                        v-for="(item, i) in faqItems"
                        :key="i"
                        class="border-b border-sidebar-border/70"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 py-5 text-left"
                            @click="toggleFaq(i)"
                        >
                            <span class="text-base font-bold">{{
                                item.q
                            }}</span>
                            <ChevronDown
                                class="h-5 w-5 shrink-0 text-muted-foreground transition-transform duration-300"
                                :class="openFaqIndex === i ? 'rotate-180' : ''"
                            />
                        </button>
                        <div
                            class="grid transition-[grid-template-rows] duration-300 ease-out"
                            :class="
                                openFaqIndex === i
                                    ? 'grid-rows-[1fr]'
                                    : 'grid-rows-[0fr]'
                            "
                        >
                            <div class="overflow-hidden">
                                <p class="pb-5 text-sm text-muted-foreground">
                                    {{ item.a }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ CTA akhir ============ -->
            <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div
                    class="overflow-hidden rounded-3xl bg-emerald-600 px-6 py-14 text-center text-white sm:px-12"
                >
                    <h2
                        class="text-2xl font-extrabold tracking-tight sm:text-4xl"
                    >
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
                    <Link href="/login" class="transition hover:text-foreground"
                        >Masuk</Link
                    >
                    <Link
                        href="/register"
                        class="transition hover:text-foreground"
                        >Daftar</Link
                    >
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
