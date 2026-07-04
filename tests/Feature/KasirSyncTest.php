<?php

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Sinkronisasi antrean transaksi offline PWA lewat rute web (sesi + CSRF),
| bukan /api. Idempoten via client_uid; server tetap otoritas (KasirService).
|--------------------------------------------------------------------------
*/

test('guests cannot hit the sync route', function () {
    $this->postJson('/kasir/transaksi/sync', [])->assertStatus(401);
});

test('kasir can sync an offline sale via the web session route', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $produk = Produk::factory()->create(['tipe_jual' => 'satuan', 'harga_jual' => 15000, 'stok' => 10]);

    $this->actingAs($kasir)->postJson('/kasir/transaksi/sync', [
        'metode_pembayaran' => 'cash',
        'bayar' => 30000,
        'client_uid' => '22222222-2222-4222-8222-222222222222',
        'items' => [['id_produk' => $produk->id_produk, 'jumlah' => 2]],
    ])->assertCreated();

    expect((float) $produk->fresh()->stok)->toBe(8.0);
    $this->assertDatabaseHas('transaksis', [
        'id_user' => $kasir->id,
        'client_uid' => '22222222-2222-4222-8222-222222222222',
    ]);
});

test('admin can also sync (role superset for solo-operator toko)', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $produk = Produk::factory()->create(['tipe_jual' => 'satuan', 'harga_jual' => 10000, 'stok' => 5]);

    $this->actingAs($admin)->postJson('/kasir/transaksi/sync', [
        'metode_pembayaran' => 'cash',
        'bayar' => 10000,
        'client_uid' => '55555555-5555-4555-8555-555555555555',
        'items' => [['id_produk' => $produk->id_produk, 'jumlah' => 1]],
    ])->assertCreated();
});

test('re-syncing the same client_uid is idempotent (no double-charge)', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $produk = Produk::factory()->create(['tipe_jual' => 'satuan', 'harga_jual' => 10000, 'stok' => 10]);

    $uid = '33333333-3333-4333-8333-333333333333';
    $payload = [
        'metode_pembayaran' => 'cash',
        'bayar' => 20000,
        'client_uid' => $uid,
        'items' => [['id_produk' => $produk->id_produk, 'jumlah' => 2]],
    ];

    $first = $this->actingAs($kasir)->postJson('/kasir/transaksi/sync', $payload)->assertCreated();
    $second = $this->actingAs($kasir)->postJson('/kasir/transaksi/sync', $payload)->assertOk();

    expect($second->json('id_transaksi'))->toBe($first->json('id_transaksi'));
    expect(Transaksi::where('client_uid', $uid)->count())->toBe(1);
    expect((float) $produk->fresh()->stok)->toBe(8.0);
});

test('sync requires a client_uid', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $produk = Produk::factory()->create(['tipe_jual' => 'satuan', 'harga_jual' => 10000, 'stok' => 10]);

    $this->actingAs($kasir)->postJson('/kasir/transaksi/sync', [
        'metode_pembayaran' => 'cash',
        'bayar' => 20000,
        'items' => [['id_produk' => $produk->id_produk, 'jumlah' => 1]],
    ])->assertStatus(422);
});

test('sync rejects insufficient stock as a conflict (422)', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $produk = Produk::factory()->create(['tipe_jual' => 'satuan', 'harga_jual' => 10000, 'stok' => 1]);

    $this->actingAs($kasir)->postJson('/kasir/transaksi/sync', [
        'metode_pembayaran' => 'cash',
        'bayar' => 50000,
        'client_uid' => '44444444-4444-4444-8444-444444444444',
        'items' => [['id_produk' => $produk->id_produk, 'jumlah' => 5]],
    ])->assertStatus(422);
});
