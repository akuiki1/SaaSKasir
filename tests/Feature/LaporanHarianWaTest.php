<?php

use App\Contracts\WhatsappSender;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Toko;
use App\Models\Transaksi;
use App\Services\LaporanHarianService;
use App\Support\TenantContext;
use Illuminate\Support\Carbon;

/*
|--------------------------------------------------------------------------
| Laporan tutup toko harian via WhatsApp: isi pesan (tier-gated), skip
| kondisi, dan command batch multi-toko yang mengunci TenantContext per toko.
|--------------------------------------------------------------------------
*/

/**
 * Buat toko + 1 transaksi (2 item) pada tanggal tertentu. Mengunci
 * TenantContext ke toko ini SEBELUM membuat model ber-scope (Produk/Transaksi/
 * DetailTransaksi) agar id_toko terisi benar.
 */
function seedTokoHarian(
    string $tier,
    ?string $whatsapp,
    int $total,
    int $modalPerItem = 4000,
    string $namaProduk = 'Kopi Sachet',
    ?Carbon $tgl = null,
): Toko {
    $tgl ??= Carbon::today();

    $toko = Toko::factory()->create(['whatsapp' => $whatsapp, 'status' => 'aktif']);
    $toko->forceFill([
        'tier' => $tier,
        'langganan_sampai' => $tier === 'gratis' ? null : Carbon::today()->addMonth(),
    ])->save();

    app(TenantContext::class)->setToko($toko);

    $produk = Produk::factory()->create(['nama' => $namaProduk, 'harga_jual' => $total]);
    $trx = Transaksi::factory()->create([
        'total_harga' => $total,
        'metode_pembayaran' => 'cash',
        'bayar' => $total,
        'kembalian' => 0,
        'created_at' => $tgl->copy()->setTime(10, 0),
    ]);
    DetailTransaksi::factory()->create([
        'id_transaksi' => $trx->id_transaksi,
        'id_produk' => $produk->id_produk,
        'jumlah' => 2,
        'harga' => intdiv($total, 2),
        'modal' => $modalPerItem,
        'subtotal' => $total,
    ]);

    return $toko->fresh();
}

/** Pengirim WA palsu yang merekam pesan (menggantikan driver via container). */
function fakeWa(): WhatsappSender
{
    $fake = new class implements WhatsappSender
    {
        /** @var list<array{tujuan: string, pesan: string}> */
        public array $terkirim = [];

        public bool $balikan = true;

        public function kirim(string $tujuan, string $pesan): bool
        {
            $this->terkirim[] = ['tujuan' => $tujuan, 'pesan' => $pesan];

            return $this->balikan;
        }
    };

    app()->instance(WhatsappSender::class, $fake);

    return $fake;
}

// ─── Isi pesan (tier-gating) ─────────────────────────────────────────────────

test('tier warung menampilkan laba kotor & margin', function () {
    $toko = seedTokoHarian('warung', '081200000001', 20000, modalPerItem: 4000);

    $pesan = app(LaporanHarianService::class)->bangunPesan($toko);

    // cogs = 4000 x 2 = 8000; laba kotor = 20000 - 8000 = 12000; margin 60%.
    expect($pesan)->toContain('Omzet: *Rp20.000*')
        ->toContain('1 transaksi')
        ->toContain('Laba kotor: *Rp12.000* (margin 60%)')
        ->toContain('Kopi Sachet (2)')
        ->not->toContain('Upgrade ke Warung');
});

test('tier gratis menyembunyikan laba & menampilkan ajakan upgrade', function () {
    $toko = seedTokoHarian('gratis', '081200000002', 50000);

    $pesan = app(LaporanHarianService::class)->bangunPesan($toko);

    expect($pesan)->toContain('Omzet: *Rp50.000*')
        ->not->toContain('Laba kotor')
        ->toContain('Upgrade ke Warung');
});

test('metode pembayaran hanya menampilkan yang bernilai', function () {
    $toko = seedTokoHarian('gratis', '081200000003', 30000);

    $pesan = app(LaporanHarianService::class)->bangunPesan($toko);

    // Semua transaksi cash → hanya Tunai muncul.
    expect($pesan)->toContain('Tunai: Rp30.000')
        ->not->toContain('QRIS')
        ->not->toContain('Transfer Bank');
});

test('tanpa transaksi pada tanggal itu tidak menghasilkan pesan', function () {
    $toko = Toko::factory()->create(['whatsapp' => '081200000004', 'status' => 'aktif']);
    app(TenantContext::class)->setToko($toko);

    expect(app(LaporanHarianService::class)->bangunPesan($toko))->toBeNull();
});

// ─── Command batch ───────────────────────────────────────────────────────────

test('command mengirim laporan ke tiap toko aktif dengan angka masing-masing', function () {
    $wa = fakeWa();

    seedTokoHarian('warung', '081200000001', 20000, namaProduk: 'Beras 5kg');
    seedTokoHarian('gratis', '081200000002', 55000, namaProduk: 'Minyak Goreng');

    $this->artisan('laporan:harian-wa')->assertSuccessful();

    expect($wa->terkirim)->toHaveCount(2);

    $pesanA = collect($wa->terkirim)->firstWhere('tujuan', '6281200000001')['pesan'];
    $pesanB = collect($wa->terkirim)->firstWhere('tujuan', '6281200000002')['pesan'];

    // Isolasi: pesan tiap toko hanya memuat omzet tokonya sendiri.
    expect($pesanA)->toContain('Rp20.000')->not->toContain('Rp55.000');
    expect($pesanB)->toContain('Rp55.000')->not->toContain('Rp20.000');
});

test('toko tanpa nomor whatsapp dilewati', function () {
    $wa = fakeWa();

    seedTokoHarian('gratis', null, 20000);

    $this->artisan('laporan:harian-wa')->assertSuccessful();

    expect($wa->terkirim)->toHaveCount(0);
});

test('toko tanpa transaksi dilewati', function () {
    $wa = fakeWa();

    Toko::factory()->create(['whatsapp' => '081200000009', 'status' => 'aktif']);

    $this->artisan('laporan:harian-wa')->assertSuccessful();

    expect($wa->terkirim)->toHaveCount(0);
});

test('opsi --toko membatasi ke satu toko', function () {
    $wa = fakeWa();

    $a = seedTokoHarian('gratis', '081200000001', 20000);
    seedTokoHarian('gratis', '081200000002', 30000);

    $this->artisan('laporan:harian-wa', ['--toko' => $a->slug])->assertSuccessful();

    expect($wa->terkirim)->toHaveCount(1);
    expect($wa->terkirim[0]['tujuan'])->toBe('6281200000001');
});
