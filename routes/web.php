<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanggananController;
use App\Http\Controllers\Admin\LaporanWaController;
use App\Http\Controllers\Ceo\DashboardController as CeoDashboardController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\OnboardingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PesananPublikController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\SikasirController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\SuperAdmin\AdminController as SuperAdminAdminController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\TokoController as SuperAdminTokoController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root `/` = landing page publik SiKasir-the-product (pintu depan SaaS, halaman
// marketing — BUKAN storefront toko). DI LUAR middleware tenant supaya SELALU
// tampil walau belum ada toko sama sekali. Namanya tetap `home` agar redirect
// yang menuju route('home') (mis. setelah logout) tetap valid. CTA-nya mengarah
// ke registrasi mandiri (/register). Storefront toko kini HANYA via /toko/{slug}
// (lihat grup tenant di bawah) — ini langkah Fase 2 yang dicatat di ResolveTenant.
Route::get('/', [SikasirController::class, 'landing'])->name('home');

// Rute publik/tamu bertenant (pesan online + storefront per toko). Middleware
// `tenant` (App\Http\Middleware\ResolveTenant) menentukan toko aktif: dari
// {tokoSlug} bila ada, atau fallback ke satu-satunya toko aktif (rute pesan
// tanpa slug — dipakai alur pemesanan single-instance klien tunggal saat ini).
Route::middleware('tenant')->group(function () {
    Route::post('pesan', [PesananPublikController::class, 'store'])
        ->middleware('throttle:15,1')
        ->name('pesan.store');

    Route::post('lacak-pesanan', [PesananPublikController::class, 'lacak'])
        ->middleware('throttle:30,1')
        ->name('pesan.lacak');

    // Storefront per toko lewat slug — jalur storefront resmi sekarang (root `/`
    // sudah jadi landing produk; lihat catatan ResolveTenant & roadmap Fase 2).
    Route::prefix('toko/{tokoSlug}')->name('toko.')->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::post('pesan', [PesananPublikController::class, 'store'])
            ->middleware('throttle:15,1')
            ->name('pesan.store');

        Route::post('lacak-pesanan', [PesananPublikController::class, 'lacak'])
            ->middleware('throttle:30,1')
            ->name('pesan.lacak');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route(match (Auth::user()?->role) {
            'ceo' => 'ceo.dashboard',
            'superadmin' => 'superadmin.dashboard',
            'admin' => 'admin.dashboard',
            default => 'kasir.dashboard',
        });
    })->name('dashboard');

    // ----- Panel platform (kantor pusat SiKasir, lintas-tenant) -----
    // CEO: murni pemantauan sistem — satu dashboard baca-saja, tanpa aksi.
    Route::middleware(['role:ceo'])->group(function () {
        Route::get('ceo/dashboard', [CeoDashboardController::class, 'index'])->name('ceo.dashboard');
    });

    // Super Admin: operasional platform — kelola toko (status, langganan,
    // onboarding toko baru) & kelola akun admin (reset password).
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('superadmin/dashboard', [SuperAdminDashboardController::class, 'index'])->name('superadmin.dashboard');

        Route::get('superadmin/toko', [SuperAdminTokoController::class, 'index'])->name('superadmin.toko');
        Route::post('superadmin/toko', [SuperAdminTokoController::class, 'store'])->name('superadmin.toko.store');
        Route::put('superadmin/toko/{toko}/status', [SuperAdminTokoController::class, 'updateStatus'])->name('superadmin.toko.status');
        Route::post('superadmin/toko/{toko}/langganan', [SuperAdminTokoController::class, 'perpanjang'])->name('superadmin.toko.langganan');

        Route::get('superadmin/admins', [SuperAdminAdminController::class, 'index'])->name('superadmin.admins');
        Route::put('superadmin/admins/{user}/password', [SuperAdminAdminController::class, 'resetPassword'])->name('superadmin.admins.password');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        // Users
        Route::get('admin/users', [UserController::class, 'index'])->name('admin.users');
        Route::post('admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        // Kategori
        Route::get('admin/kategori', [KategoriController::class, 'index'])->name('admin.kategori');
        Route::post('admin/kategori', [KategoriController::class, 'store'])->name('admin.kategori.store');
        Route::put('admin/kategori/{kategori}', [KategoriController::class, 'update'])->name('admin.kategori.update');
        Route::delete('admin/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('admin.kategori.destroy');

        // Pelanggan (umum & reseller)
        Route::get('admin/pelanggan', [PelangganController::class, 'index'])->name('admin.pelanggan');
        Route::post('admin/pelanggan', [PelangganController::class, 'store'])->name('admin.pelanggan.store');
        Route::put('admin/pelanggan/{pelanggan}', [PelangganController::class, 'update'])->name('admin.pelanggan.update');
        Route::delete('admin/pelanggan/{pelanggan}', [PelangganController::class, 'destroy'])->name('admin.pelanggan.destroy');

        // Produk
        Route::get('admin/products', [ProdukController::class, 'index'])->name('admin.products');
        Route::post('admin/products', [ProdukController::class, 'store'])->name('admin.products.store');
        Route::post('admin/products/generate-all-barcodes', [ProdukController::class, 'generateAllBarcodes'])->name('admin.products.generate-all');
        Route::post('admin/products/{produk}/restore', [ProdukController::class, 'restore'])->name('admin.products.restore');
        Route::delete('admin/products/{produk}/force', [ProdukController::class, 'hapusPermanen'])->name('admin.products.force-delete');
        Route::put('admin/products/{produk}', [ProdukController::class, 'update'])->name('admin.products.update');
        Route::delete('admin/products/{produk}', [ProdukController::class, 'destroy'])->name('admin.products.destroy');

        // Transaksi
        Route::get('admin/transactions', [TransaksiController::class, 'index'])->name('admin.transactions');
        Route::post('admin/transactions', [TransaksiController::class, 'store'])->name('admin.transactions.store');
        Route::put('admin/transactions/{transaksi}', [TransaksiController::class, 'update'])->name('admin.transactions.update');
        Route::delete('admin/transactions/{transaksi}', [TransaksiController::class, 'destroy'])->name('admin.transactions.destroy');

        // Pengeluaran
        Route::get('admin/pengeluarans', [PengeluaranController::class, 'index'])->name('admin.pengeluarans');
        Route::post('admin/pengeluarans', [PengeluaranController::class, 'store'])->name('admin.pengeluarans.store');
        Route::put('admin/pengeluarans/{pengeluaran}', [PengeluaranController::class, 'update'])->name('admin.pengeluarans.update');
        Route::delete('admin/pengeluarans/{pengeluaran}', [PengeluaranController::class, 'destroy'])->name('admin.pengeluarans.destroy');

        // Manajemen Stok (kartu stok, stok masuk/keluar, opname)
        Route::get('admin/stok', [StokController::class, 'index'])->name('admin.stok');
        Route::post('admin/stok/masuk', [StokController::class, 'masuk'])->name('admin.stok.masuk');
        Route::post('admin/stok/keluar', [StokController::class, 'keluar'])->name('admin.stok.keluar');
        Route::post('admin/stok/penyesuaian', [StokController::class, 'penyesuaian'])->name('admin.stok.penyesuaian');

        // Produksi (batch costing produk buatan sendiri)
        Route::get('admin/produksi', [ProduksiController::class, 'index'])->name('admin.produksi');
        Route::post('admin/produksi', [ProduksiController::class, 'store'])->name('admin.produksi.store');
        Route::delete('admin/produksi/{produksi}', [ProduksiController::class, 'destroy'])->name('admin.produksi.destroy');

        // Onboarding (sekali-jalan setelah registrasi mandiri): unduh template & impor produk massal.
        Route::get('admin/onboarding', [OnboardingController::class, 'index'])->name('admin.onboarding');
        Route::get('admin/onboarding/template', [OnboardingController::class, 'template'])->name('admin.onboarding.template');
        Route::post('admin/onboarding/import', [OnboardingController::class, 'import'])->name('admin.onboarding.import');

        // Laporan / Analisis
        // Laporan Keuangan (Laba Rugi) di balik paywall Warung+ — lihat config/langganan.php.
        Route::get('admin/laporan/keuangan', [LaporanController::class, 'keuangan'])
            ->middleware('fitur:laporan_keuangan')
            ->name('admin.laporan.keuangan');
        Route::get('admin/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('admin.laporan.penjualan');
        Route::get('admin/laporan/inventaris', [LaporanController::class, 'inventaris'])->name('admin.laporan.inventaris');
        Route::get('admin/laporan/pelanggan', [LaporanController::class, 'pelanggan'])->name('admin.laporan.pelanggan');

        // Langganan (status tier, perbandingan, riwayat, CTA upgrade).
        Route::get('admin/langganan', [LanggananController::class, 'index'])->name('admin.langganan');

        // Preview laporan tutup toko WA (JSON) — kartu info di dashboard admin.
        Route::get('admin/laporan-wa/preview', [LaporanWaController::class, 'preview'])->name('admin.laporan-wa.preview');

        // Promo
        Route::get('admin/promos', [PromoController::class, 'index'])->name('admin.promos');
        Route::post('admin/promos', [PromoController::class, 'store'])->name('admin.promos.store');
        Route::put('admin/promos/{promo}', [PromoController::class, 'update'])->name('admin.promos.update');
        Route::delete('admin/promos/{promo}', [PromoController::class, 'destroy'])->name('admin.promos.destroy');

        // Pesanan online (kelola dari sisi admin — komponen sama dengan kasir)
        Route::get('admin/pesanan', [PesananController::class, 'index'])->name('admin.pesanan');
        Route::post('admin/pesanan/{pesanan}/siap', [PesananController::class, 'siap'])->name('admin.pesanan.siap');
        Route::post('admin/pesanan/{pesanan}/edit', [PesananController::class, 'edit'])->name('admin.pesanan.edit');
        Route::post('admin/pesanan/{pesanan}/proses', [PesananController::class, 'proses'])->name('admin.pesanan.proses');
        Route::post('admin/pesanan/{pesanan}/batal', [PesananController::class, 'batal'])->name('admin.pesanan.batal');
    });

    Route::middleware(['role:kasir'])->group(function () {
        Route::get('kasir/dashboard', [KasirController::class, 'dashboard'])->name('kasir.dashboard');
        Route::get('kasir/transaksi', [KasirController::class, 'transaksi'])->name('kasir.transaksi');
        Route::get('kasir/pelanggan/cari', [KasirController::class, 'cariPelanggan'])->name('kasir.pelanggan.cari');
        Route::post('kasir/transaksi', [KasirController::class, 'store'])->name('kasir.transaksi.store');
        // Sinkronisasi antrean transaksi offline PWA → JSON (bukan Inertia). Memakai
        // sesi web + CSRF yang sama dengan halaman kasir (andal same-origin, tak
        // bergantung SANCTUM_STATEFUL_DOMAINS). Idempoten via client_uid.
        Route::post('kasir/transaksi/sync', [KasirController::class, 'sync'])->name('kasir.transaksi.sync');
        Route::get('kasir/riwayat', [KasirController::class, 'riwayat'])->name('kasir.riwayat');
        Route::get('kasir/riwayat/cetak', [KasirController::class, 'riwayatCetak'])->name('kasir.riwayat.cetak');

        // Pesanan online (pending) — proses pembayaran saat pelanggan ambil barang.
        Route::get('kasir/pesanan', [PesananController::class, 'index'])->name('kasir.pesanan');
        Route::post('kasir/pesanan/{pesanan}/siap', [PesananController::class, 'siap'])->name('kasir.pesanan.siap');
        Route::post('kasir/pesanan/{pesanan}/edit', [PesananController::class, 'edit'])->name('kasir.pesanan.edit');
        Route::post('kasir/pesanan/{pesanan}/proses', [PesananController::class, 'proses'])->name('kasir.pesanan.proses');
        Route::post('kasir/pesanan/{pesanan}/batal', [PesananController::class, 'batal'])->name('kasir.pesanan.batal');
    });
});

require __DIR__.'/settings.php';
