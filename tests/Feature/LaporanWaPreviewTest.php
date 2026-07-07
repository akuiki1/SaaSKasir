<?php

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Carbon;

/*
|--------------------------------------------------------------------------
| Preview laporan tutup toko WA di dashboard admin (JSON, reuse
| LaporanHarianService — isi preview = isi yang akan dikirim command).
|--------------------------------------------------------------------------
*/

test('admin can preview the daily WA report for today', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $produk = Produk::factory()->create(['nama' => 'Kopi Sachet', 'harga_jual' => 25000]);
    $trx = Transaksi::factory()->create([
        'id_user' => $admin->id,
        'total_harga' => 50000,
        'metode_pembayaran' => 'cash',
        'bayar' => 50000,
        'kembalian' => 0,
        'created_at' => Carbon::today()->setTime(10, 0),
    ]);
    DetailTransaksi::factory()->create([
        'id_transaksi' => $trx->id_transaksi,
        'id_produk' => $produk->id_produk,
        'jumlah' => 2,
        'harga' => 25000,
        'modal' => 4000,
        'subtotal' => 50000,
    ]);

    $respon = $this->actingAs($admin)
        ->getJson(route('admin.laporan-wa.preview'))
        ->assertOk();

    expect($respon->json('pesan'))
        ->toContain('Omzet')
        ->toContain('Kopi Sachet');
});

test('preview returns null pesan when there are no transactions today', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->getJson(route('admin.laporan-wa.preview'))
        ->assertOk()
        ->assertJson(['pesan' => null]);
});

test('kasir cannot access the WA report preview', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);

    $this->actingAs($kasir)
        ->getJson(route('admin.laporan-wa.preview'))
        ->assertForbidden();
});

test('admin dashboard exposes the laporan_wa info card props', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Dashboard')
            ->has('laporan_wa.jam_kirim')
            ->has('laporan_wa.tujuan')
            // Dev/CI memakai driver 'log' → belum aktif.
            ->where('laporan_wa.aktif', false)
        );
});
